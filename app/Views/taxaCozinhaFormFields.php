<div class="row g-3 mt-0">
    <div class="col-12 col-lg-6">
        <div class="section-box">
            <h5><i class="bi bi-cash-coin"></i> Dados da taxa</h5>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Valor da taxa</label>
                    <input type="text" class="form-control money" name="valor_taxa" placeholder="R$ 5.000,00">
                </div>
                <div class="col-12">
                    <label class="form-label d-block">Taxa embutida no contrato?</label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="embutida_contrato" id="embutidaSimCreate" value="1" checked>
                        <label class="form-check-label" for="embutidaSimCreate">Sim</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="embutida_contrato" id="embutidaNaoCreate" value="0">
                        <label class="form-check-label" for="embutidaNaoCreate">Não</label>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label d-block">Houve negociação?</label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="houve_negociacao" id="negociacaoNaoCreate" value="0" checked>
                        <label class="form-check-label" for="negociacaoNaoCreate">Não</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="houve_negociacao" id="negociacaoSimCreate" value="1">
                        <label class="form-check-label" for="negociacaoSimCreate">Sim</label>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label">Detalhes da negociação</label>
                    <textarea class="form-control" name="detalhes_negociacao" placeholder="Desconto de R$1.000 aprovado pelo Rodrigo"></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-6">
        <div class="section-box">
            <h5><i class="bi bi-exclamation-triangle"></i> Status financeiro</h5>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label d-block">Status</label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="status_financeiro" id="statusPendenteCreate" value="pendente" checked>
                        <label class="form-check-label" for="statusPendenteCreate">Pendente</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="status_financeiro" id="statusPagoCreate" value="pago">
                        <label class="form-check-label" for="statusPagoCreate">Pago</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="status_financeiro" id="statusParcialCreate" value="parcial">
                        <label class="form-check-label" for="statusParcialCreate">Parcial</label>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Forma de pagamento</label>
                    <select class="form-select" name="forma_pagamento">
                        <option value="PIX">PIX</option>
                        <option value="Transferência">Transferência</option>
                        <option value="Boleto">Boleto</option>
                        <option value="Dinheiro">Dinheiro</option>
                        <option value="Cartão">Cartão</option>
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Valor pago</label>
                    <input type="text" class="form-control money" name="valor_pago" placeholder="R$ 4.000,00">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Data pagamento</label>
                    <input type="date" class="form-control" name="data_pagamento">
                </div>
                <div class="col-12">
                    <label class="form-label">Comprovante</label>
                    <input type="file" class="form-control" name="comprovante" accept=".pdf,.jpg,.jpeg,.png,.webp">
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-6">
        <div class="section-box">
            <h5><i class="bi bi-clipboard-check"></i> Controle operacional</h5>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label d-block">Cobrança realizada?</label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="cobranca_realizada" id="cobrancaSimCreate" value="1" checked>
                        <label class="form-check-label" for="cobrancaSimCreate">Sim</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="cobranca_realizada" id="cobrancaNaoCreate" value="0">
                        <label class="form-check-label" for="cobrancaNaoCreate">Não</label>
                    </div>
                </div>
                <div class="col-12 col-md-5">
                    <label class="form-label">Data da cobrança</label>
                    <input type="date" class="form-control" name="data_cobranca">
                </div>
                <div class="col-12">
                    <label class="form-label">Observações</label>
                    <textarea class="form-control" name="observacoes" placeholder="Cliente solicitou prazo até semana do evento"></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-6">
        <div class="section-box">
            <h5><i class="bi bi-sliders"></i> Controle avançado</h5>
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label class="form-label">Responsável pelo pagamento</label>
                    <select class="form-select" name="responsavel_pagamento">
                        <option value="Cliente">Cliente</option>
                        <option value="Contratante">Contratante</option>
                        <option value="Produção">Produção</option>
                        <option value="Fornecedor">Fornecedor</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label d-block">Taxa prevista em contrato?</label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="prevista_contrato" id="previstaSimCreate" value="1" checked>
                        <label class="form-check-label" for="previstaSimCreate">Sim</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="prevista_contrato" id="previstaNaoCreate" value="0">
                        <label class="form-check-label" for="previstaNaoCreate">Não</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
