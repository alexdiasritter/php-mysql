# Táxi Dog

PWA de controle de corridas e faturamento. PHP + MySQL, sem framework e sem
dependências — sobe direto em hospedagem compartilhada.

## Estrutura

```
taxidog/
├── index.php         Só redireciona /taxidog/ para frontend/.
│
├── frontend/         O app inteiro. Uma página = um arquivo (dados + HTML).
│   ├── index.php         login
│   ├── home.php          menu principal
│   ├── schedule.php      agendar corrida
│   ├── pendentes.php     corridas agendadas / combinados
│   ├── earnings.php      dashboard de ganhos
│   ├── history.php       histórico semanal
│   ├── history-month.php histórico mensal
│   ├── update_ride.php   salva a edição do modal de pendentes.php
│   ├── get_ride.php  check_notifications.php    (esses dois respondem JSON)
│   └── header.php  footer.php                   layout comum
│
├── backend/          Só PHP. Nada aqui abre pelo navegador (.htaccess).
│   ├── bootstrap.php     carrega tudo, nesta ordem
│   ├── config.php        credenciais e ajustes
│   ├── config.local.php  (opcional) sobrescreve o config.php — não versionar
│   ├── database.php      conexão PDO única, criada sob demanda
│   ├── helpers.php       e(), money(), asset(), page_url(), redirect(), datas...
│   ├── auth.php          Session, Users e Auth (login e cookie "lembrar-me")
│   ├── rides.php         todo o SQL de corridas
│   ├── earnings.php      números do dashboard
│   └── notifications.php avisos
│
├── styles/           base.css  components.css  rides.css  +  pages/*.css
├── js/               app.js  login.js  schedule.js  pendentes.js  earnings.js
│
├── database/schema.sql      Estrutura das tabelas
├── tools/gerar_senha.php    Troca senha de usuário (só via linha de comando)
└── manifest.json  sw.js  app-icon.png    Arquivos do PWA (ficam na raiz para
                                          o Service Worker cobrir o app todo)
```

## Como funciona uma página

Cada arquivo de `frontend/` busca os dados e imprime o HTML, entre o header e o
footer. `frontend/home.php`:

```php
require_once __DIR__ . '/../backend/bootstrap.php';  // config, banco, sessão, auth
Auth::requireLogin();                                // exige login

$username = Auth::username();                        // dados da tela

$pageTitle   = 'Início · Táxi Dog';                  // lidos pelo header/footer
$pageStyles  = ['styles/pages/home.css'];            // opcional
$pageScripts = ['js/home.js'];                       // opcional

require __DIR__ . '/header.php';
?>
<h2>Olá, <?php echo e($username); ?>!</h2>
<?php require __DIR__ . '/footer.php';
```

O header inclui os CSS/JS globais mais os que a página pediu em `$pageStyles`
e `$pageScripts` — sempre com caminho a partir da raiz do app.

Para criar uma tela nova: um arquivo em `frontend/`, nesse mesmo formato.

### Links e redirects

As páginas linkam umas para as outras com caminho relativo (`href="home.php"`),
porque todas estão na mesma pasta. Já em PHP use `page_url()`, que devolve o
caminho a partir do domínio:

```php
redirect(page_url('pendentes.php?sucesso=atualizado'));
```

## Configuração

Edite `backend/config.php` ou, melhor, copie `backend/config.local.php.example`
para `backend/config.local.php` e coloque lá as credenciais reais.

Em produção, mude `'debug' => false`: isso esconde os erros do PHP na tela e faz
a tela de login parar de dizer se o erro foi no usuário ou na senha.

## Trocar a senha de um usuário

```
php tools/gerar_senha.php admin novaSenha
```

O login aceita tanto senha em texto puro (formato antigo do banco) quanto hash
do `password_hash()`, então dá para migrar um usuário por vez.

## Cache do PWA

O `sw.js` só guarda arquivos estáticos. Ao mudar a lista de `urlsToCache`,
aumente o `CACHE_NAME` para forçar a atualização nos celulares já instalados.
Os CSS/JS têm `?v=<data do arquivo>` na URL, então mudanças aparecem sem
precisar limpar cache.
