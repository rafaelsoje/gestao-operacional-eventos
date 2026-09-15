const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

function worker() {
  const handlers = {};
  const stored = new Map([['/offline.html', new Response('OFFLINE')]]);
  const deleted = [];
  const pending = [];
  const cache = {
    put: async (req, response) => stored.set(req.url || req, response),
    match: async (req) => stored.get(req.url || req),
  };
  const context = {
    URL,
    self: {
      registration: { scope: 'https://example.invalid/' },
      location: { origin: 'https://example.invalid' },
      addEventListener: (name, handler) => { handlers[name] = handler; },
      clients: { claim: async () => {} },
    },
    caches: {
      open: async () => cache,
      match: cache.match,
      keys: async () => ['operacional-runtime-v3', 'operacional-static-v3', 'other-app'],
      delete: async (key) => deleted.push(key),
    },
    fetch: async () => new Response('PRIVATE', { headers: { 'Content-Type': 'text/html' } }),
  };
  vm.runInNewContext(fs.readFileSync('public/service-worker.js', 'utf8'), context);
  return {
    context, stored, deleted,
    async fetch(path, destination = 'document') {
      let response;
      handlers.fetch({
        request: { method: 'GET', url: `https://example.invalid${path}`, destination, mode: destination === 'document' ? 'navigate' : 'cors' },
        respondWith: (promise) => { response = promise; },
        waitUntil: (promise) => pending.push(promise),
      });
      const result = await response;
      await Promise.all(pending);
      return result;
    },
    async activate() {
      handlers.activate({ waitUntil: (promise) => pending.push(promise) });
      await Promise.all(pending);
    },
  };
}

test('private HTML is never cached and offline never returns old session HTML', async () => {
  const w = worker();
  assert.equal(await (await w.fetch('/usuarios')).text(), 'PRIVATE');
  assert.equal(w.stored.has('https://example.invalid/usuarios'), false);
  w.stored.set('https://example.invalid/usuarios', new Response('OLD SESSION'));
  w.context.fetch = async () => { throw new Error('offline'); };
  assert.equal(await (await w.fetch('/usuarios')).text(), 'OFFLINE');
});

test('activation purges previous application caches only', async () => {
  const w = worker();
  await w.activate();
  assert.deepEqual(w.deleted, ['operacional-runtime-v3', 'operacional-static-v3']);
});

test('private avatars and uploads are not intercepted or cached', async () => {
  const w = worker();
  assert.equal(await w.fetch('/avatar/person.jpg', 'image'), undefined);
  assert.equal(await w.fetch('/uploads/2026-09-15/private.jpg', 'image'), undefined);
  assert.equal(w.stored.size, 1);
});

test('public assets are cached but HTML responses are not', async () => {
  const w = worker();
  await w.fetch('/assets/build/app.js', 'script');
  assert.equal(w.stored.has('https://example.invalid/assets/build/app.js'), false);
  w.context.fetch = async () => new Response('script', { headers: { 'Content-Type': 'text/javascript' } });
  await w.fetch('/assets/build/app.js', 'script');
  assert.equal(w.stored.has('https://example.invalid/assets/build/app.js'), true);
});
