<?php

use CodeIgniter\Test\CIUnitTestCase;

final class ViewSecurityTest extends CIUnitTestCase
{
    public function testEventEditFormsTreatStoredMarkupAsText(): void
    {
        $payload = '\"><img data-xss="yes" src=x onerror=alert(1)></textarea><script data-xss="yes">alert(1)</script>';
        foreach (['cadastroOperacional', 'cadastroPlanejamento'] as $view) {
            $source = file_get_contents(APPPATH . 'Views/' . $view . '.php');
            preg_match_all('/\$item->(\w+)/', $source, $matches);
            $item = (object)array_fill_keys($matches[1], 0);
            $item->id = 1;
            $item->nome = $payload;
            $item->descricao = $payload;
            $item->data = $payload;
            $item->tipo = 'Social';
            $html = view($view, ['item' => $item], ['saveData' => false]);
            $dom = new DOMDocument();
            @$dom->loadHTML($html);
            $xpath = new DOMXPath($dom);
            $this->assertSame(0, $xpath->query('//*[@data-xss]')->length, $view);
            $this->assertSame($payload, $xpath->query('//input[@name="nome"]')->item(0)->getAttribute('value'));
            $this->assertSame($payload, $xpath->query('//textarea[@name="descricao"]')->item(0)->textContent);
            $this->assertSame($payload, $xpath->query('//input[@name="data"]')->item(0)->getAttribute('value'));
        }
    }

    public function testUserViewsEscapeStoredNames(): void
    {
        $payload = '\"><img data-xss="yes" src=x onerror=alert(1)>';
        $user = (object)[
            'id' => 1, 'name' => $payload, 'email' => 'test@example.invalid',
            'role' => 'user', 'admin' => 0, 'avatar' => 'avatar.svg',
        ];
        session()->set('user', $user);
        try {
            foreach (['usuarios', 'updateUsuario'] as $view) {
                $html = view($view, ['user' => $user, 'users' => [$user]], ['saveData' => false]);
                $dom = new DOMDocument();
                @$dom->loadHTML($html);
                $xpath = new DOMXPath($dom);
                $this->assertSame(0, $xpath->query('//*[@data-xss]')->length, $view);
                if ($view === 'updateUsuario') {
                    $this->assertSame($payload, $xpath->query('//input[@name="name"]')->item(0)->getAttribute('value'));
                }
            }
        } finally {
            session()->remove('user');
        }
    }
}
