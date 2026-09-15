# Deploy do audiovisual

O repositório local é a fonte de alterações. `github` é a cópia do código no GitHub; `vps` é o bare da produção, fora da raiz pública.

```bash
composer install
vendor/bin/phpunit --no-coverage
composer audit --locked
npm ci
npm run build
git add app tests composer.json composer.lock public/assets/build
git commit
git push github master
git push vps master
```

Os bundles de produção são versionados para não precisar instalar Node na VPS. Atualizá-los sempre que houver alteração no frontend; source maps de produção estão desabilitados.

O push da branch master na VPS executa o hook post-receive, que chama o publicador instalado em /usr/local/sbin/publicar-audiovisual.py. Ele exporta a revisão recebida, instala as dependências do lock como rafael, valida a sintaxe, ajusta permissões e troca /var/www/audiovisual/current. Um teste HTTPS de login/cookies falho reverte o link e a configuração. Uma falha no hook não desfaz o push: conferir a saída e o arquivo REVISION da publicação.

Dados persistentes continuam em /var/www/audiovisual/.env e /var/www/audiovisual/writable; cada release aponta para esses caminhos. Avatares legados em public/media são preservados fora do Git. Nginx serve somente current/public. A tarefa subscription:sync usa current.

Não versionar segredos, sessões, dados de usuários ou dumps SQL. O GitHub é backup do código, não do banco nem dos uploads. Esses dados dependem do backup criptografado da VPS.

Cookies usam Secure por padrão. Em desenvolvimento HTTP, definir `cookie.secure = false` somente no .env local. Não sobrescrever com false em produção. A URL pode ser ajustada por `app.baseURL` no .env.

Uploads de avatar aceitam JPG/PNG com extensão e MIME correspondentes, até 5 MiB e 20 milhões de pixels. O fallback também exige conteúdo válido. Comprovantes aceitam PDF/JPG/PNG/WEBP com MIME correspondente e limite de 20 MiB.

Rollback: um administrador aponta current para a release anterior e recarrega PHP-FPM/Nginx, validando o site. Isso não desfaz migrações de banco. Esta publicação de segurança não executa migrações nem cobranças Asaas.
