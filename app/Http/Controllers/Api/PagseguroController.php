<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notificacao;
use App\Models\Transacoes;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PagseguroController extends Controller
{
    private $_link, $_token, $_ambiente, $_email;

    private $_status = [
        '1' => 'Aguardando autorização do pagamento',
        '2' => 'Pagamento em análise',
        '3' => 'Pagamento aprovado',
        '4' => 'Pagamento aprovado',
        '5' => 'Outros',
        '6' => 'Pagamento devolvida',
        '7' => 'Pagamento cancelado',
        '100' => 'Promocional'
    ];

    public function __get($propriedade)
    {
        if (in_array($propriedade, ['_ambiente'])) :
            return $this->$propriedade;
        endif;
        return false;
    }

    public function __construct()
    {
        $this->_ambiente = "sandbox";
        // $this->_link = "https://ws.sandbox.pagseguro.uol.com.br";
        // $this->_token = "BD3206F42F9047B6937CC8E968FB3C2E";
        $this->_link = "https://ws.pagseguro.uol.com.br";
        $this->_token = "0c22b7ad-7c41-4396-806a-0dbb7f08e87bc0375ea34a7889aa856c25a07cfc2eed15ee-e833-4f9d-a3f6-cccc7e468d30";
        $this->_email = "swami3d@gmail.com";
    }

    public function ambiente()
    {
        return $this->_ambiente;
    }

    public function status($id)
    {
        if (isset($this->_status[$id])) :
            return $this->_status[$id];
        endif;
        return 'SEM STATUS DEFINIDO';
    }

    private function erro($erro)
    {
        $erros = [
            'bad_request' => 'O pagamento não pode ser processado',
            '5003' => 'falha de comunicação com a instituição financeira',
            '10000' => 'bandeira inválida',
            '10001' => "número do cartão inválido",
            '10002' => "data no formato inválido",
            '10003' => "código de segurança inválido",
            '10004' => "cvv obrigatório",
            '10006' => "código de segurança inválido",
            '53004' => "quantidade de ítens inválido",
            '53005' => "moeda obrigatória",
            '53006' => "moeda inválida",
            '53007' => "tamanho do campo referência inválido",
            '53008' => "url de notificação inválida",
            '53009' => "tamanho da url de notificação inválida",
            '53010' => "email do comprador inválido.",
            '53011' => "email do comprador inválido",
            '53012' => "tamanho do campo email do comprador inválido",
            '53013' => "nome do comprador obrigatório",
            '53014' => "nome do comprador inválido",
            '53015' => "tamanho do campo nome do comprador inválido",
            '53017' => "cpf do comprador inválido",
            '53018' => "código de área do telefone inválido",
            '53019' => "tamanho do campo código de área do telefone inválido",
            '53020' => "telefone obrigatório",
            '53021' => "telefone inválido",
            '53022' => "CEP obrigatório",
            '53023' => "CEP inválido",
            '53024' => "rua do endereço obrigatório",
            '53025' => "rua do endereço inválido",
            '53026' => "número do endereço obrigatório",
            '53027' => "número do endereço inválido",
            '53028' => "complemento do endereço inválido",
            '53029' => "bairro do endereço obrigatório",
            '53030' => "bairro do endereço inválido",
            '53031' => "cidade do endereço obrigatória",
            '53032' => "cidade do endereço inválida",
            '53033' => "estado do endereço obrigatório",
            '53034' => "estado do endereço inválido",
            '53035' => "país do endereço obrigatório.",
            '53036' => "país do endereço inválido",
            '53037' => "token do cartão de crédito obrigatório",
            '53038' => "quantidade de parcelas obrigatória",
            '53039' => "quantidade de parcelas inválida",
            '53040' => "valor da parcela obrigatória",
            '53041' => "valor da parcela inválida",
            '53042' => "portador do cartão obrigatório",
            '53043' => "portador do cartão inválido",
            '53044' => "tamanho do campo portador do cartão inválido",
            '53045' => "CPF do portador do cartão obrigatório",
            '53046' => "CPF do portador do cartão inválido",
            '53047' => "data de nascimento do portador do cartão obrigatório",
            '53048' => "data de nascimento do portador do cartão inválido",
            '53049' => "código de área do portador do cartão obrigatório",
            '53050' => "código de área do portador do cartão inválido",
            '53051' => "telefone do portador do cartão obrigatório",
            '53052' => "telefone  do portador do cartão inválido",
            '53053' => "CEP do portador do cartão obrigatório",
            '53054' => "CEP do portador do cartão inválido",
            '53055' => "rua do portador do cartão obrigatório",
            '53056' => "rua do portador do cartão inválido",
            '53057' => "número do endereço do portador do cartão obrigatório",
            '53058' => "número do endereço do portador do cartão inválido",
            '53059' => "tamanho do campo complemento do endereço do portador do cartão inválido",
            '53060' => "bairro do portador do cartão obrigatório",
            '53061' => "tamanho do campo bairro do portador do cartão inválido",
            '53062' => "cidade do portador do cartão obrigatório",
            '53063' => "tamanho do campo cidade do portador do cartão inválido",
            '53064' => "estado do portador do cartão obrigatório",
            '53065' => "estado do portador do cartão inválido",
            '53066' => "país do portador do cartão obrigatório",
            '53067' => "tamanho do campo país do portador do cartão inválido",
            '53068' => "tamanho do email do vendedor inválido",
            '53069' => "email do vendedor inválido",
            '53070' => "código do ítem obrigatório",
            '53071' => "tamanho do código do ítem inválido",
            '53072' => "descrição do ítem obrigatório",
            '53073' => "tamanho do campo ítem inválido",
            '53074' => "quantidade de ítens obrigatória",
            '53075' => "quantidade de ítens fora do limite",
            '53076' => "quantidade de ítens inválido",
            '53077' => "montante do ítem obrigatório",
            '53078' => "montante do ítem inválido.",
            '53079' => "montante fora do limite",
            '53081' => "comprador é igual ao vendedor",
            '53084' => "vendedor inválido, verifique se é uma conta com status de vendedor",
            '53085' => "método de pagamento indisponível",
            '53086' => "montante total acima do limite do cartão",
            '53087' => "dados do cartão inválidos",
            '53091' => "hash do comprador inválido",
            '53092' => "bandeira do cartão não aceita",
            '53095' => "tipo de entrega inválido",
            '53096' => "custo de entrega inválido",
            '53097' => "custo da entrega fora do limite",
            '53098' => "valor total é negatívo",
            '53099' => "montante extra inválido.",
            '53101' => "modo de pagamento inválido, valores válidos são default e gateway",
            '53102' => "método de pagamento inválido, valores válidos são creditCard, boleto e eft",
            '53104' => "custo de entrega informado, endereço de entrega deve ser completo",
            '53105' => "informações do comprador informado, email também deve ser informado",
            '53106' => "portador do cartão incompleto",
            '53109' => "endereço do comprador informado, email do comprador também deve ser informado",
            '53110' => "banco eft obrigatório",
            '53111' => "banco eft não aceito",
            '53115' => "data de nascimento do comprador inválida",
            '53117' => "CPNJ do comprador inválido",
            '53122' => "domínio do email do comprador inválido. Você deve usar um email @sandbox.pagseguro.com.br",
            '53140' => "quantidade de parcelas fora do limite. O valor deve ser maior que zero",
            '53141' => "comprador bloqueado",
            '53142' => "token do cartão de crédito inválido",
            '14007' => "status da transação não permite reembolso",
        ];
        return $erros[$erro] ?? NULL;
    }

    private function curl($url, $dados = [], $header = [], $method = 'POST')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        $dados = http_build_query($dados);

        // if ($method == 'GET' && !empty($dados)) :
        //     $dados_string = "";
        //     foreach ($dados as $key => $value) {
        //         $dados_string .= $key . '=' . $value . '&';
        //     }
        //     rtrim($dados_string, '&');
        //     $dados = $dados_string;
        // else :
        // endif;

        if ($dados) curl_setopt($ch, CURLOPT_POSTFIELDS, $dados);
        if ($header) curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        return curl_exec($ch);
    }

    public function sessao()
    {
        $url = $this->_link . '/v2/sessions?email=' . $this->_email . '&token=' . $this->_token;

        $sessao = @simplexml_load_string($this->curl($url));

        if (isset($sessao->id)) :
            return response([
                'error' => false,
                'code' => (string)$sessao->id
            ]);
        endif;
        return response([
            'error' => true,
            'message' => 'Tente novamente mais tarde!',
            'error_message' => $sessao
        ], 400);
    }

    public function transacao($transacao)
    {
        $url = $this->_link . '/v2/transactions/' . $transacao . '?email=' . $this->_email . '&token=' . $this->_token;
        $xml = $this->curl($url, [], [], 'GET');
        $xml = simplexml_load_string($xml);

        if (isset($xml->error)) :
            return response(["erro" => true, "titulo" => "Código inválido!", "texto" => "Verifique o códito e tente novamente."], 400);
        endif;

        return response()->json($xml);
    }

    public function referencia($referencia)
    {
        $url = $this->_link . '/v2/transactions?email=' . $this->_email . '&token=' . $this->_token . '&reference=' . $referencia;
        $xml = $this->curl($url, [], [], 'GET');
        $xml = simplexml_load_string($xml);

        if ($xml->resultsInThisPage <= 0) :
            return response(["erro" => true, "titulo" => "Não encontrado", "texto" => "Nenhum resultado com a referência digitada."], 400);
        endif;

        return response()->json($xml);
    }

    public function getNotificacao($notificacao)
    {
        $url = $this->_link . '/v3/transactions/notifications/' . $notificacao . '/?email=' . $this->_email . '&token=' . $this->_token;
        $xml = $this->curl($url, [], [], 'GET');
        $xml = simplexml_load_string($xml);

        if (isset($xml->error)) :
            return response(["erro" => true, "titulo" => "Não encontrado", "texto" => "Nenhum resultado com a notificação digitada."], 400);
        endif;

        return response()->json($xml);
    }

    public function notificacao(Request $dados)
    {
        $Notificacao = new Notificacao;
        $Notificacao->codigo = $dados->notificationCode;

        $Notificacao->save();

        $url = $this->_link . '/v3/transactions/notifications/' . $dados->notificationCode . '/?email=' . $this->_email . '&token=' . $this->_token;
        $xml = $this->curl($url, [], [], 'GET');
        $xml = simplexml_load_string($xml);

        $User = new User();
        $user = $User->where('uuid', $xml->reference)->first();

        if ($user) :
            if ((int)$xml->status === 3) :
                $date = date("Y-m-d");

                $tempo = strtotime($date . ' + 30 days');

                if ($user->plano == 1) {
                    $tempo = strtotime($date . ' + 30 days');
                } else if ($user->plano == 2) {
                    $tempo = strtotime($date . ' + 180 days');
                } else if ($user->plano == 3) {
                    $tempo = strtotime($date . ' + 360 days');
                };

                $update = [
                    'data_plano' => $date,
                    'data_vencimento' => date('Y-m-d', $tempo),
                    'status' => 1
                ];

                $User = new User();
                $User->where('id', $user->id)->update($update);

                $array = [
                    'titulo' => 'Pagamento aprovado!',
                    'texto' => 'Você já pode acessar nossos cursos. Basta ir em nosso site, na área do aluno e entrar com seu usuário e senha!',
                    'button' => (object)[
                        'texto' => 'ÁREA DO ALUNO',
                        'url' => 'https://leap.art.br/login'
                    ],
                    'nome' => $user->nome,
                    'email' => $user->email
                ];

            else :
                $array = [
                    'titulo' => 'Status da compra',
                    'texto' => $this->status((int)$xml->status),
                    'button' => false,
                    'nome' => $user->nome,
                    'email' => $user->email
                ];
            endif;

            $Email = new Email();
            return $Email->enviar($array);
        endif;
    }

    public function checkout(Request $dados)
    {
        $user = auth('api')->user();

        // Formatação de dados
        $dados->senderPhone = preg_replace("/[^0-9]/", "", $dados->senderPhone);
        $dados->creditCardHolderPhone = preg_replace("/[^0-9]/", "", $dados->creditCardHolderPhone);
        $dados->billingAddressPostalCode = preg_replace("/[^0-9]/", "", $dados->billingAddressPostalCode);
        $dados->senderCPF = preg_replace("/[^0-9]/", "", $dados->senderCPF);
        $dados->senderCPF = str_pad($dados->senderCPF, 11, '0', STR_PAD_LEFT);
        $dados->creditCardHolderCPF = preg_replace("/[^0-9]/", "", $dados->creditCardHolderCPF);
        $dados->creditCardHolderCPF = str_pad($dados->creditCardHolderCPF, 11, '0', STR_PAD_LEFT);

        // Dados da API
        $array['token'] = $this->_token;
        $array['creditCardToken'] = $dados->cartao_token;
        $array['senderHash'] = $dados->hash;
        $array['paymentMode'] = 'default';
        $array['paymentMethod'] = 'creditCard';
        $array['receiverEmail'] = $this->_email;
        $array['currency'] = 'BRL';

        // Produtos comprados
        $i = 1;
        foreach ($dados->item as $r) :
            $array['itemId' . $i] = $r["itemId"];
            $array['itemDescription' . $i] = $r["itemDescription"];
            $array['itemAmount' . $i] = number_format($r["itemAmount"], 2, '.', '');
            $array['itemQuantity' . $i] = (int)$r["itemQuantity"];
            $i++;
        endforeach;

        // Dados do comprador
        $array['reference'] = $user->uuid;
        $array['senderName'] = $dados->senderName;
        $array['senderCPF'] = $dados->senderCPF;
        $array['senderAreaCode'] = $dados->senderAreaCode;
        $array['senderPhone'] = $dados->senderPhone;
        $array['senderEmail'] = $dados->senderEmail;

        // Dados do parcelamento
        $array['installmentQuantity'] = (int)$dados->installmentQuantity;

        $array['noInterestInstallmentQuantity'] = 12;
        $array['installmentValue'] = number_format($dados->installmentValue, 2, '.', '');

        // Dados do cartão
        $array['creditCardHolderName'] = $dados->creditCardHolderName;
        $array['creditCardHolderCPF'] = $dados->creditCardHolderCPF;
        $array['creditCardHolderBirthDate'] = $dados->creditCardHolderBirthDate;
        $array['creditCardHolderAreaCode'] = $dados->creditCardHolderAreaCode;
        $array['creditCardHolderPhone'] = $dados->creditCardHolderPhone;

        // Endereço do cartão
        $array['billingAddressStreet'] = $dados->billingAddressStreet;
        $array['billingAddressNumber'] = $dados->billingAddressNumber;
        $array['billingAddressComplement'] = $dados->billingAddressComplement;
        $array['billingAddressDistrict'] = $dados->billingAddressDistrict;
        $array['billingAddressPostalCode'] = $dados->billingAddressPostalCode;
        $array['billingAddressCity'] = $dados->billingAddressCity;
        $array['billingAddressState'] = $dados->billingAddressState;
        $array['billingAddressCountry'] = 'BRA';

        // Endereço de entrega
        $array['shippingAddressRequired'] = false;

        // Requisição para api do pagseguro
        $url = $this->_link . '/v2/transactions?email=' . $this->_email;
        $headers = ['Content-Type: application/x-www-form-urlencoded; charset=UTF-8'];

        $xml = $this->curl($url, $array, $headers);
        $xml = simplexml_load_string($xml);

        // Validação de retorno
        if (isset($xml->error)) :
            $erro_texto = $this->erro((string)$xml->error->code);
            return response([
                'erro' => true,
                'titulo' => 'Ocorreu um erro!',
                'texto' => !empty($erro_texto) ? $erro_texto : 'Ocorreu um erro no pagamento.<!-- ' . (string)$xml->error->message . '-->'
            ], 400);
        else :

            $Transacoes = new Transacoes();
            $Transacoes->uuid = Str::uuid();
            $Transacoes->usuario = 1;
            $Transacoes->referencia = $user->uuid;
            $Transacoes->transacao = $xml->code;
            $Transacoes->plano = $dados->plano;
            $Transacoes->data_criacao = date("Y-m-d H:i:s");
            $Transacoes->status = 1;
            $Transacoes->save();

            $User = new User();
            $User->where('id', $user->id)->update(['plano' => $dados->plano]);

            $array = [
                'titulo' => 'Status da compra',
                'texto' => 'Aguardando autorização do pagamento',
                'nome' => $user->nome,
                'button' => false,
                'email' => $user->email
            ];

            $Email = new Email();
            $Email->enviar($array);

            return response([
                'erro' => false,
                'message' => 'Pagamento enviado com sucesso!'
            ]);
        endif;
    }
}
