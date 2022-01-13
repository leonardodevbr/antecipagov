<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Proposal;
use App\Services\AntecipaGov\AntecipaGovService;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PHPUnit\Exception;

class AntecipaGovController extends Controller
{
    private $antecipaGov;

    public function __construct(AntecipaGovService $antecipaGov)
    {
        $this->antecipaGov = $antecipaGov;
    }

    public function availableQuotes(Request $request)
    {
        $uri = "ObterCotacaoDisponivel";

        return $this->antecipaGov->makeRequestPortal($uri, [], 'GET');
    }

    public function getBankingTerm(Request $request)
    {
        if (empty($request->input('code'))) {
            return $this->antecipaGov->reponseError("Código inválido ou não enviado");
        }

        $data = [
            "idCotacao" => $request->input('code')
        ];

        return $this->antecipaGov->makeRequestPortal("ObterTermoDomicilioBancario", $data, 'GET');
    }

    public function getContractPDF(Request $request)
    {
        if (empty($request->input('code'))) {
            return $this->antecipaGov->reponseError("Código inválido ou não enviado");
        }

        if (empty($request->input('year_number'))) {
            return $this->antecipaGov->reponseError("Ano inválido ou não enviado");
        }

        if (empty($request->input('uasg'))) {
            return $this->antecipaGov->reponseError("UASG inválido ou não enviado");
        }

        $data = [
            "idCotacao" => $request->input('code'),
            "nrAnoContrato" => $request->input('year_number'),
            "uasg" => $request->input('uasg')
        ];

        return $this->antecipaGov->makeRequestPortal("FornecerPDFContrato", $data, 'GET');
    }

    public function sendProposal(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->request->add(['code' => uniqid("PD-")]);
            $proposal = Proposal::create($request->except('contracts'));

            if ($proposal->save()) {
                foreach ($request->input('contracts') as $contractData) {
                    $contractData['proposal_id'] = $proposal->id;
                    $contract = Contract::create($contractData);
                    $contract->save();
                }
            }

            $proposal->load('contracts');

            $data = [
                "idProposta" => $proposal->code,
                "valorLiquido" => $proposal->net_amount,
                "valorEmprestimo" => $proposal->amount,
                "numeroParcelas" => $proposal->quota_qty,
                "valorParcela" => $proposal->quota_amount,
                "fluxoPagamento" => $proposal->payment_flow,
                "txJuros" => $proposal->tax,
                "txJurosMora" => $proposal->late_tax,
                "valorMulta" => $proposal->fine_amount,
                "valorSeguro" => $proposal->insurance_amount,
                "valorIOF" => $proposal->iof,
                "valorTAC" => $proposal->tac,
                "valorCET" => $proposal->cet,
                "inModalidade" => $proposal->modality,
                "dataLiberacao" => $proposal->release_date->format('Y-m-d'),
                "dataUltimaParcela" => $proposal->last_quota_at->format('Y-m-d'),
                "codInstituicao" => "40050004000107",
                "nomeInstituicao" => "Plataforma",
                "codPlataforma" => "40050004000107",
                "nomePlataforma" => "Plataforma",
                "linkPlataforma" => "https://plataforma.br/" . $proposal->id,
                "idCotacao" => $proposal->external_proposal_id
            ];

            $response = $this->antecipaGov->makeRequestPortal('IncluirProposta', $data);

            if ($response['success']) {
                DB::commit();
                return $this->antecipaGov->reponseSuccess('Proposta enviada com sucesso!', $proposal->toArray());
            }

            return $this->antecipaGov->reponseError("Erro eo enviar a proposta.", $response, 500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->antecipaGov->reponseError($e->getMessage());
        }
    }

    public function consultProposal(Request $request)
    {
        $code = $request->input('code');

        if (empty($code)) {
            return $this->antecipaGov->reponseError("Código inválido ou não enviado");
        }

        $proposal = Proposal::whereCode($code)
            ->with('contracts')
            ->first();

        if (empty($proposal)) {
            return $this->antecipaGov->reponseError("Nenhuma proposta encontrada com o código " . $code);
        }

        $data = [
            'NumeroProposta' => $code
        ];

        $response = $this->antecipaGov->makeRequestPortal("ConsultarProposta", $data, 'GET');

        if ($response['success']) {
            DB::beginTransaction();
            try {

                $proposal->accepted = $response['data']->aceita;
                $proposal->save();

                DB::commit();

                return $this->antecipaGov->reponseSuccess('Proposta consultada com sucesso!', $proposal->toArray());
            } catch (Exception $e) {
                DB::rollBack();
                return $this->antecipaGov->reponseError($e->getMessage());
            }
        }

        return $this->antecipaGov->reponseError("Erro eo consultar a proposta.", $response, 500);
    }

    public function consultExternalProposals(Request $request)
    {
        if (empty($request->input('external_proposal_id'))) {
            return $this->antecipaGov->reponseError("ID da cotação inválido ou não enviado");
        }

        $data = [
            'numeroCotacao' => $request->input('external_proposal_id')
        ];

        $response = $this->antecipaGov->makeRequestPortal("ConsultarCotacaoComProposta", $data, 'GET');

        if ($response['success']) {
            return $this->antecipaGov->reponseSuccess('Cotação consultada com sucesso!', $response['data']);
        }

        return $this->antecipaGov->reponseError("Erro eo consultar a cotação.", $response, 500);
    }

    public function consultContracts(Request $request)
    {
        $code = $request->input('code');

        if (empty($code)) {
            return $this->antecipaGov->reponseError("Código inválido ou não enviado");
        }

        $proposal = Proposal::whereCode($code)->first();

        if (empty($proposal)) {
            return $this->antecipaGov->reponseError("Nenhuma proposta encontrada com o código " . $code);
        }

        $niFornecedor = $proposal->account->document;
        $idPedido = $proposal->external_proposal_id;

        $contracts = [];
        foreach ($proposal->contracts as $contract) {
            $contractData = [
                "uasg" => [
                    "uasg" => $contract->uasg
                ],
                "nrAnoContrato" => $contract->year_number
            ];
            array_push($contracts, $contractData);
        }

        $data = [
            "contratos" => $contracts
        ];

        $response = $this->antecipaGov->makeRequestPilar("contratos/consulta/$niFornecedor/$idPedido", $data);

        if ($response['success']) {
            return $this->antecipaGov->reponseSuccess('Contratos consultados com sucesso!', $response['data']);
        }

        return $this->antecipaGov->reponseError("Erro eo consultar os contratos.", $response, 500);
    }


    public function registerCreditTransaction(Request $request)
    {
        $code = $request->input('code');

        if (empty($code)) {
            return $this->antecipaGov->reponseError("Código inválido ou não enviado");
        }

        $proposal = Proposal::whereCode($code)->first();

        if (empty($proposal)) {
            return $this->antecipaGov->reponseError("Nenhuma proposta encontrada com o código " . $code);
        }

        $contracts = [];
        foreach ($proposal->contracts as $contract) {
            $contractData = [
                "uasg" => [
                    "uasg" => $contract->uasg
                ],
                "nrAnoContrato" => $contract->year_number
            ];
            array_push($contracts, $contractData);
        }

        $data = [
            "niFornecedor" => $proposal->account->document,
            "saldoDevedorAtualizado" => $proposal->amount,
            "cnpjInstituicaoFinanceira" => "00000000000001",
            "cnpjInstituicaoFinanceiraConta" => "00000000000001",
            "cnpjInstituicaoPlataforma" => "00000000000002",
            "codigoInstituicaoContaVinculada" => "11111",
            "nrAgenciaContaVinculada" => "9999",
            "nrContaVinculada" => "12345-6",
            "numeroInstrumento" => "INST-01",
            "dataAtualizacao" => Carbon::now()->format('Y-m-d H:i'),
            "contratos" => $contracts,
            "dataCelebracaoOperacao" => Carbon::now()->format('Y-m-d'),
            "dataFinalReal" => Carbon::now()->format('Y-m-d'),
            "dataFinalVigencia" => Carbon::now()->format('Y-m-d'),
            "dataInicialVigencia" => Carbon::now()->format('Y-m-d'),
            "idPedidoPortal" => $proposal->external_proposal_id,
            "valorOperacao" => $proposal->amount
        ];

        return $this->antecipaGov->makeRequestPilar("operacoes/registro", $data, 'GET');
    }

    public function amortizeCreditTransaction(Request $request)
    {
        $data = [
            "cnpjInstituicaoFinanceira" => "40050004000107",
            "cnpjInstituicaoFinanceiraConta" => "40050004000107",
            "cnpjInstituicaoPlataforma" => "40050004000107",
            "dataAtualizacao" => Carbon::now(),
            "idOperacaoCredito" => $request->input('operation_id'),
            "niFornecedor" => $request->input('document'),
            "saldoDevedorAtualizado" => $request->input('amount')
        ];

        return $this->antecipaGov->makeRequestPilar("operacoes/amortizacao", $data, 'GET');
    }

    public function settleCreditTransaction(Request $request)
    {
        $data = [
            "cnpjInstituicaoFinanceira" => "40050004000107",
            "cnpjInstituicaoFinanceiraConta" => "40050004000107",
            "cnpjInstituicaoPlataforma" => "40050004000107",
            "dataAtualizacao" => Carbon::now(),
            "idOperacaoCredito" => $request->input('operation_id'),
            "niFornecedor" => $request->input('document'),
            "saldoDevedorAtualizado" => $request->input('amount')
        ];

        return $this->antecipaGov->makeRequestPilar("operacoes/liquidacao", $data, 'GET');
    }

    public function cancelCreditTransaction(Request $request)
    {
        $data = [
            "cnpjInstituicaoFinanceira" => "40050004000107",
            "cnpjInstituicaoFinanceiraConta" => "40050004000107",
            "cnpjInstituicaoPlataforma" => "40050004000107",
            "dataAtualizacao" => Carbon::now(),
            "idOperacaoCredito" => $request->input('operation_id'),
            "niFornecedor" => $request->input('document'),
            "saldoDevedorAtualizado" => $request->input('amount')
        ];

        return $this->antecipaGov->makeRequestPilar("operacoes/cancelamento", $data, 'GET');
    }

    public function getCreditTransaction(Request $request)
    {
        $operationId = $request->input('operation_id');
        return $this->antecipaGov->makeRequestPilar("operacoes/$operationId", [], 'GET');
    }


    public function getStatusCreditTransaction(Request $request)
    {
        $operationId = $request->input('operation_id');
        return $this->antecipaGov->makeRequestPilar("operacoes/$operationId/status", [], 'GET');
    }
}
