# GuPayment

[![Build Status](https://travis-ci.org/Potelo/GuPayment.svg?branch=master)](https://travis-ci.org/Potelo/GuPayment)
## Introdução

GuPayment é baseado no Laravel Cashier e fornece uma interface para controlar assinaturas do iugu.com

## Instalação Laravel 5.x

Instale esse pacote pelo composer:

```
composer require potelo/gu-payment
```

Adicione o ServiceProvider em config/app.php

```php
Potelo\GuPayment\GuPaymentServiceProvider::class,
```

Agora, configure as variáveis utilizadas pelo GuPayment no seu .env:

```
IUGU_APIKEY=SUA_CHAVE
IUGU_ID=SEU_ID_IUGU
GUPAYMENT_SIGNATURE_TABLE=subscriptions
IUGU_MODEL=User
IUGU_MODEL_FOREIGN_KEY=user_id
IUGU_USER_MODEL_COLUMN=iugu_id
IUGU_SUBSCRIPTION_MODEL_ID_COLUMN=iugu_id
IUGU_SUBSCRIPTION_MODEL_PLAN_COLUMN=iugu_plan
```

Antes de usar o GuPayment você precisa preparar o banco de dados. Primeiro você tem que publicar o migration.

```
php artisan vendor:publish --tag=migrations
```

Caso precise modificar ou acrescentar colunas na tabela de assinatura, basta editar os migrations publicados. Depois, basta rodar o comando php artisan migrate.

Vamos agora adicionar o Trait ao seu modelo do usuário.

```php
use Potelo\GuPayment\GuPaymentTrait;

class User extends Authenticatable
{
    use GuPaymentTrait;
}
```

Agora vamos adicionar em config/services.php duas configurações. A classe do usuário, sua chave de api que o Iugu fornece 
e o nome da tabela utilizada para gerenciar as assinaturas, a mesma escolhida na criação do migration.

```php
'iugu' => [
    'model'  => App\User::class,
    'key' => env('IUGU_APIKEY'),
    'signature_table' => env('GUPAYMENT_SIGNATURE_TABLE'),
    'model_foreign_key' => env('IUGU_MODEL_FOREIGN_KEY'),
]
```

## Assinaturas

### Criando assinaturas

Para criar uma assinatura, primeiro você precisa ter uma instância de um usuário que extende o GuPaymentTrait. Você então deve usar o método `newSubscription` para criar uma assinatura:
```php
$user = User::find(1);

$user->newSubscription('main', 'gold')->create($creditCardToken);
```
O primeiro argumento deve ser o nome da assinatura. Esse nome não será utilizado no Iugu.com, apenas na sua aplicação. Se sua aplicação tiver apenas um tipo de assinatura, você pode chamá-la de principal ou primária. O segundo argumento é o identificador do plano no Iugu.com.

O método `create` automaticamente criará uma assinatura no Iugu.com e atualizará o seu banco de dados com o ID do cliente referente ao Iugu e outras informações relevantes. Você pode chamar o `create` sem passar nenhum parâmetro ou informar o token do cartão de crédito para que o usuário tenha uma forma de pagamento padrão. Veja como gerar o token em [iugu.js](https://iugu.com/referencias/iugu-js)

Caso queira que a assinatura seja criada apenas após a comprovação do pagamento, basta chamar o método  `chargeOnSuccess` após `newSubscription`. **IMPORTANTE**: Esse modo de criar uma assinatura só funciona para o cliente que tenha um método de pagamento padrão, não funciona com boleto.

```php
$user = User::find(1);

$user->newSubscription('main', 'gold')
->chargeOnSuccess()
->create($creditCardToken);
```

### Assinatura com subitens

Para adicionar itens de cobrança a mais na assinatura do cliente, utilize o método `subItems`.
```php
$subItems = [
    [
        'description' => 'Desconto recorrente',
        'price_cents' => -900,
        'quantity' => 1,
        'recurrent' => true,
    ],
    [
        'description' => 'Adicional não recorrente',
        'price_cents' => 250,
        'quantity' => 1,
        'recurrent' => false,
    ]
];

// Create Subscription
$user->newSubscription('main', 'gold')
    ->subItems($subItems)
    ->create($creditCardToken);
``` 

Também é possível adicionar um item por vez, utilizando o método `addSubItem`.

```php
$subItem = [
   'description' => 'Desconto recorrente',
   'price_cents' => -900,
   'quantity' => 1,
   'recurrent' => true,
];

// Create Subscription
$user->newSubscription('main', 'gold')
    ->addSubItem($subItem)
    ->create($creditCardToken);
``` 

#### Dados adicionais
Se você desejar adicionar informações extras à assinatura, basta passar um array como terceiro parâmetro no método `newSubscription`, que é repassado à API do Iugu no parâmetro `custom_variables`:
```php
$user = User::find(1);

$user->newSubscription('main', 'gold', [
    'adicional_assinatura' => 'boa assinatura'
])->create(NULL);
```

#### Outros parâmetros
Para customizar os parâmetros enviados à API, passe um array no quarto parâmetro do método `newSubscription` para a criação da assinatura, e/ou no segundo parâmetro do método `create` para a criação do cliente:
```php
$user = User::find(1);

'$user->newSubscription('main', 'gold', [], ['ignore_due_email' => true])
    ->create(NULL, [
        'name' => $user->nome,
        'notes' => 'Anotações gerais'
    ]);
```

Para mais informações dos parâmetros que são suportados pela API do Iugu, confira a [Documentação oficial](https://dev.iugu.com/reference#criar-assinatura)


### Tratamento de erros

Caso algum erro seja gerado no Iugu, é possível identificar esses erros pelo método `getLastError` do SubscriptionBuilder:

```php
$user = User::find(1);

$subscriptionBuilder = $user->newSubscription('main', 'gold');

$subscription = $subscriptionBuilder->trialDays(20)->create($creditCardToken);

if ($subscription) {
    // TUDO ok
} else {
    $erros = $subscriptionBuilder->getLastError();
    
    if (is_array($erros)) {
        // array
    } else {
        // string
    }
}
```

O erro retornado pelo iugu, pode ser um array ou uma string.

### Checando status da assinatura

Uma vez que o usuário assine um plano na sua aplicação, você pode verificar o status dessa assinatura através de alguns métodos. O método `subscribed` retorna **true** se o usuário possui uma assinatura ativa, mesmo se estiver no período trial:
```php
if ($user->subscribed('main')) {
    //
}
```

O método `subscribed`pode ser utilizado em um route middleware, permitindo que você filtre o acesso de rotas baseado no status da assinatura do usuário:

```php
public function handle($request, Closure $next)
{
    if ($request->user() && ! $request->user()->subscribed('main')) {
        // This user is not a paying customer...
        return redirect('billing');
    }

    return $next($request);
}
```

Se você precisa saber se um a assinatura de um usuário está no período trial, você pode usar o método `onTrial`. Esse método pode ser útil para informar ao usuário que ele está no período de testes, por exemplo:
```php
if ($user->subscription('main')->onTrial()) {
    //
}
```
O método `onPlan` pode ser usado para saber se o usuário está assinando um determinado plano. Por exemplo, para verificar se o usuário assina o plano **gold**:
```php
if ($user->onPlan('gold')) {
    //
}
```

Para saber se uma assinatura foi cancelada, basta usar o método `cancelled` na assinatura:
```php
if ($user->subscription('main')->cancelled()) {
    //
}
```
Você também pode checar se uma assinatura foi cancelada mas o usuário ainda se encontra no "período de carência". Por exemplo, se um usuário cancelar a assinatura no dia 5 de Março mas a data de vencimento é apenas no dia 10, ele está nesse período de carência até o dia 10. Para saber basta utilizar o método `onGracePeriod`:

```php
if ($user->subscription('main')->onGracePeriod()) {
    //
}
```


Para utilizar o objeto do Iugu a partir da assinatura, utilize o método `asIuguSubscription`:

```php
$user->subscription('main')->asIuguSubscription();
```


### Mudando o plano da assinatura
Se um usuário já possui uma assinatura, ele pode querer mudar para algum outro plano. Por exemplo, um usuário do plano **gold** pode querer economizar e mudar para o plano **silver**. Para mudar o plano de um usuário em uma assinatura, basta usar o método `swap` da seguinte forma:

```php
$user = App\User::find(1);

$user->subscription('main')->swap('silver');
```
### Cancelando assinaturas
Para cancelar uma assinatura, basta chamar o método `cancel` na assinatura do usuário:
```php
$user->subscription('main')->cancel();
```

### Reativando assinaturas
Se um usuário tem uma assinatura cancelada e gostaria de reativá-la, basta utilizar o método `resume`. Ele precisa está no "período de carência" para conseguir reativá-la:
```php
$user->subscription('main')->resume();
```
## Assinatura trial

Se você desejar oferecer um período trial para os usuários, você pode usar o método `trialDays` ao criar uma assinatura:
```php
$user = User::find(1);

$user->newSubscription('main', 'gold')
            ->trialDays(10)
            ->create($creditCardToken);
```
O usuário só será cobrado, após o período trial. Lembrando que para verificar se um usuário está com a assinatura no período trial, basta chamar o método `onTrial`:
```php
if ($user->subscription('main')->onTrial()) {
    //
}
```

O método `chargeOnSuccess` não funciona na criação de assinatura com trial. Caso queira validar o cartão de crédito
do usuário, você pode utilizar o método `validateCard` na criação da assinatura. O que vai ser feito no iugu é uma cobrança
de R$ 1,00 e depois o estorno dessa cobrança. Caso o pagamento seja realizado com sucesso, a assinatura é criada:

```
$user = $this->createUser();

// Create Subscription
$user->newSubscription('main', 'gold')->validateCard()->create($this->getTestToken());
```


## Tratando os gatilhos (ou Webhooks)
[Gatilhos (ou Webhooks)](https://iugu.com/referencias/gatilhos) são endereços (URLs) para onde a Iugu dispara avisos (Via método POST) para certos eventos que ocorrem em sua conta. Por exemplo, se uma assinatura do usuário for cancelada e você precisar registrar isso em seu banco, você pode usar o gatilho. Para utilizar você precisa apontar uma rota para o método `handleWebhook`, a mesma rota que você configurou no seu painel do Iugu:
```php
Route::post('webhook', '\Potelo\GuPayment\Http\Controllers\WebhookController@handleWebhook');
```
O GuPayment tem métodos para atualizar o seu banco de dados caso uma assinatura seja suspensa ou ela expire. Apontando a rota para esse método, isso ocorrerá de forma automática.
Lembrando que você precisa desativar a [proteção CRSF](https://laravel.com/docs/5.2/routing#csrf-protection) para essa rota. Você pode colocar a URL em `except` no middleware `VerifyCsrfToken`:
```php
protected $except = [
   'webhook',
];
```
### Outros gatilhos
O Iugu possui vários outros gatilhos e para você criar para outros eventos basta estender o `WebhookController`. Seus métodos devem corresponder a **handle** + o nome do evento em "camelCase". Por exemplo, ao criar uma nova fatura, o Iugu envia um gatilho com o seguinte evento: `invoice.created`, então basta você criar um método chamado `handleInvoiceCreated`.
```php
Route::post('webhook', 'MeuWebhookController@handleWebhook');
```

```php
<?php

namespace App\Http\Controllers;

use Potelo\GuPayment\Http\Controllers\WebhookController;

class MeuWebhookController extends WebhookController {

    public function handleInvoiceCreated(array $payload)
    {
        return 'Fatura criada: ' . $payload['data']['id'];
    }
}
```

Caso queira testar os webhooks em ambiente local, você pode utilizar o [ngrok](https://ngrok.com/).
## Faturas
Você pode facilmente pegar as faturas de um usuário através do método `invoices`:
```php
$invoices = $user->invoices();
```
Esse método irá trazer apenas as faturas que já foram pagas, caso queira incluir as faturas pendentes, basta passar o primeiro parâmetro como `true`:
```php
$invoices = $user->invoices(true);
```
Você pode listar as faturas de um usuário e disponibilizar pdfs de cada uma delas. Por exemplo:
```html
<table>
  @foreach ($user->invoices() as $invoice)
    <tr>
      <td>{{ $invoice->date() }}</td>
      <td>{{ $invoice->total() }}</td>
      <td><a href="/user/invoice/{{ $invoice->id }}">Download</a></td>
    </tr>
  @endforeach
</table>
```
Para gerar o pdf basta utilizar o método `downloadInvoice`:
```php
return $user->downloadInvoice($invoiceId, [
        'vendor'  => 'Sua Empresa',
        'product' => 'Seu Produto',
    ]);
```

### Reembolsar Fatura

Para reembolsar uma fatura utilize o método `refund`.
```php
// Iugu aceita cobranças em centavos
$user->refund($invoiceId);
```


## Clientes e métodos de Pagamento (Cartões)

Para gerenciar os métodos de pagamento, o cliente precisa existir no Iugu. Quando você utiliza o método `newSubscription` o cliente é criado automaticamente. Porém para criar um cliente manualmente, você pode utilizar o método `createAsIuguCustomer`.
```php
// Criar cliente no Iugu
$user->createAsIuguCustomer();

// Criar cliente no Iugu com token do cartão de crédito
$user->createAsIuguCustomer($creditCardToken);
```

Para acessar o cliente do Iugu a partir do usuário, utilize o método `asIuguCustomer`:
```php
$iuguCustomer = $user->asIuguCustomer();
```

Após ter um cliente cadastrado no Iugu, você pode gerenciar seus métodos de pagamento. Para criar um cartão utilize o método `createCard`:

```php
$user->createCard($creditCardToken);
```

O método aceita um array como segundo argumento com as opções disponíveis para criação de um método de pagamento. O cartão é criado sendo definido como `default` nos cartões do cliente. Se quiser alterar esse comportamento passe a chave `set_as_default` com o valor `false` nas opções do segundo parâmetro do método:

```
$user->createCard($creditCardToken, [
    'set_as_default' => false,
]);
```

Para obter os cartões de um cliente você pederá utilizar os métodos `cards` (Retorna uma `Illuminate\Support\Collection` de cartões), `findCard` (Retorna uma instância de `Potelo\GuPayment\Card` ou `null` se o cartão não for encontrado) ou `findCardOrFail` (Retorna uma instância de `Potelo\GuPayment\Card` ou lança uma exceção caso o cartão não seja encontrado):

```php
// Coleção de cartões
$user->cards();

// Um cartão ou null
$card = $user->findCard($cardId);

try {
    $card = $user->findCardOrFail($cardId);
} catch(Exception $e) {
    //
}
```

Para deletar um cartão apenas obtenha uma instância de `Potelo\GuPayment\Card` e use o metodo `deleteCard`:

```php
$card = $user->findCard($cardId);

$user->deleteCard($card);

```

Para deletar todos os cartões use `deleteCards`:

```php
$user->deleteCards();
```


## Cobrança simples

Se você quiser fazer uma cobrança simples com o cartão de crédito, você pode usar o método de `charge` em uma instância de um usuário que use o Trait `GuPaymentTrait`. Para utilizar a cobrança simples nesse pacote, é necessário que o cliente já esteja cadastrado no Iugu.

```php
// Iugu aceita cobranças em centavos
$user->charge(100);
```

O método `charge` aceita um array como segundo parâmetro, permitindo que você passe algumas opções desejadas para criação de uma cobrança no Iugu. Consulte a [documentação do Iugu](https://dev.iugu.com/v1.0/reference#testinput-1) para saber as opções disponíveis ao criar uma cobrança:

```php
$user->charge(100, [
    'customer_payment_method_id' => $card->id,
]);
```

Por padrão um item será criado com as seguintes definições:

```
description = 'Nova cobrança'
quantity = 1
price_cents = Valor do primeiro parâmetro
```

Sinta-se livre para adicionar seus próprios items como preferir no segundo parâmetro:

```
$user->charge(null, [
    'items' => [
        ['description' => 'Primeiro Item', 'quantity' => 10, 'price_cents' => 200],
        ['description' => 'Segundo Item', 'quantity' => 2, 'price_cents' => 200],
    ]
]);
```

OBS: Se um array de items for passado no segundo argumento o item padrão não será adicionado.
