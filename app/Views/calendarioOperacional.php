<?= $this->extend('main');?>
<?php $this->section('content');?>

<style>
.calendar-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(340px,1fr));
  gap: 1.25rem;
  padding: 1.5rem;
}
.calendar-box {
  background:#f8f9fa; 
  border:1px solid #ddd;
  border-radius:6px;
  padding:.5rem;
}
.calendar-box h3 {
  font-size:1.1rem;
  text-align:center;
  margin:.25rem 0 .5rem;
}
#calendar-year-select { max-width:200px; margin: 1rem auto; }
button{ height: 50%; }
.table th, .table td { vertical-align: middle; }

/* Fundo azul nos eventos */
.fc .fc-daygrid-event {
  background-color: #0d6efd !important;
  border-color: #0d6efd !important;
  color: #fff !important;
}
.fc .fc-daygrid-event .fc-event-title,
.fc .fc-daygrid-event .fc-event-time {
  color: #fff !important;
}
</style>

<div class="container py-4">
  <h2 class="text-center mb-3">Calendário de Eventos - <?= esc($ano) ?></h2>

  <!-- seletor de ano -->
  <div class="d-flex justify-content-center mb-4">
    <form method="get" action="" class="d-flex justify-content-center align-items-center gap-2" id="formAno">
      <input type="number" class="form-control" id="calendar-year-select" name="ano" value="<?= esc($ano) ?>" min="1970" max="2100">
      <button type="submit" class="btn btn-success">Pesquisar</button>
    </form>
  </div>

  <div class="calendar-grid">
    <?php
      $nomesMes = [
        1=>'Janeiro',2=>'Fevereiro',3=>'Março',4=>'Abril',5=>'Maio',6=>'Junho',
        7=>'Julho',8=>'Agosto',9=>'Setembro',10=>'Outubro',11=>'Novembro',12=>'Dezembro'
      ];
      for ($m=1;$m<=12;$m++):
        $id = 'cal-m'.$m;
    ?>
      <div class="calendar-box">
        <h3><?= $nomesMes[$m]; ?></h3>
        <div id="<?= $id ?>"></div>
      </div>
    <?php endfor; ?>
  </div>
</div>

<!-- Modal Evento -->
<div class="modal fade" id="eventoModal" tabindex="-1" aria-labelledby="eventoModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h1 class="modal-title fs-5" id="eventoModalLabel">Detalhes do Evento</h1>
          <small class="text-muted"><strong>Visualizar:</strong> <span id="modal-id"></span></small>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>

      <div class="modal-body">
        <!-- Cabeçalho do evento -->
        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <p class="mb-1"><strong>Título:</strong> <span id="modal-title"></span></p>
            <p class="mb-1"><strong>Tipo:</strong> <span id="modal-type"></span></p>
          </div>
          <div class="col-md-6">
            <p class="mb-1"><strong>Início:</strong> <span id="modal-start"></span></p>
            <p class="mb-1"><strong>Fim:</strong> <span id="modal-end"></span></p>
            <p class="mb-0"><strong>Descrição:</strong> <span id="modal-descricao"></span></p>
          </div>
        </div>

        <!-- Abas -->
        <ul class="nav nav-tabs" id="eventoTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-principais-tab" data-bs-toggle="tab" data-bs-target="#tab-principais" type="button" role="tab" aria-controls="tab-principais" aria-selected="true">Principais</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-custos-tab" data-bs-toggle="tab" data-bs-target="#tab-custos" type="button" role="tab" aria-controls="tab-custos" aria-selected="false">Custos</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-consumos-tab" data-bs-toggle="tab" data-bs-target="#tab-consumos" type="button" role="tab" aria-controls="tab-consumos" aria-selected="false">Consumos</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-publico-tab" data-bs-toggle="tab" data-bs-target="#tab-publico" type="button" role="tab" aria-controls="tab-publico" aria-selected="false">Público</button>
          </li>
        </ul>

        <div class="tab-content pt-3">
          <div class="tab-pane fade show active" id="tab-principais" role="tabpanel" aria-labelledby="tab-principais-tab">
            <div id="conteudo-principais" class="table-responsive"></div>
          </div>
          <div class="tab-pane fade" id="tab-custos" role="tabpanel" aria-labelledby="tab-custos-tab">
            <div id="conteudo-custos" class="table-responsive"></div>
          </div>
          <div class="tab-pane fade" id="tab-consumos" role="tabpanel" aria-labelledby="tab-consumos-tab">
            <div id="conteudo-consumos" class="table-responsive"></div>
          </div>
          <div class="tab-pane fade" id="tab-publico" role="tabpanel" aria-labelledby="tab-publico-tab">
            <div id="conteudo-publico" class="table-responsive"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const ano    = <?= (int)$ano ?>;
  const events = <?= $fcEvents ?? '[]' ?>;
  const fmtBRL = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });

  // Renderiza 12 calendários (1 por mês) para visão anual contínua.
  for (let mes=1; mes<=12; mes++) {
    const el = document.getElementById('cal-m'+mes);
    if (!el) continue;

  // Auto-scroll para o mês atual quando estiver vendo o ano corrente.
  (function scrollToCurrentMonth() {
    const now = new Date();
    const currentYear  = now.getFullYear();
    const currentMonth = now.getMonth() + 1; // 1-12

    // só auto-rola se o ano exibido for o ano atual
    if (ano !== currentYear) {
      // opcional: rolar para o topo ao abrir um ano diferente
      // window.scrollTo({ top: 0, behavior: 'auto' });
      return;
    }

    const target = document.getElementById('cal-m' + currentMonth);
    if (!target) return;

    // Se você tiver um header fixo, ajuste o offset aqui:
    const HEADER_OFFSET = 80; // px (ajuste conforme seu layout)
    // aguarda um tick para garantir layout/render concluidos
    requestAnimationFrame(() => {
      const y = target.getBoundingClientRect().top + window.pageYOffset - HEADER_OFFSET;
      window.scrollTo({ top: y, behavior: 'smooth' });
    });
  })();

    const evMes = filtrarEventosPorMes(events, ano, mes);
    const cal = new FullCalendar.Calendar(el, {
      plugins: [FullCalendar.dayGridPlugin],
      locales: FullCalendar.globalLocales || [],
      locale: 'pt-br',
      initialView: 'dayGridMonth',
      initialDate: `${ano}-${String(mes).padStart(2,'0')}-01`,
      headerToolbar: false,
      height: 'auto',
      events: evMes,
      eventClick: onEventClick,
      eventDisplay: 'block'
    });
    cal.render();
  }

  function filtrarEventosPorMes(all, year, month) {
    const ini = new Date(year, month-1, 1, 0,0,0);
    const end = new Date(year, month, 0, 23,59,59);
    return all.filter(ev => {
      const start = toDate(ev.start);
      const evEnd = ev.end ? toDate(ev.end) : start;
      return (evEnd >= ini && start <= end);
    });
  }

  function toDate(str) {
    if (typeof str !== 'string') return new Date(str);
    return new Date(str.replace(' ', 'T'));
  }

  function formatarData(dateObj) {
    return dateObj.toLocaleString('pt-BR');
  }

  function renderTabela(objeto) {
    const table = document.createElement('table');
    table.className = 'table table-sm table-striped table-bordered mb-0';
    const tbody = document.createElement('tbody');
    Object.entries(objeto).forEach(([k, v]) => {
      if (!v) return;
      const tr = document.createElement('tr');
      const th = document.createElement('th'); th.textContent = k;
      const td = document.createElement('td'); td.textContent = v;
      tr.appendChild(th); tr.appendChild(td);
      tbody.appendChild(tr);
    });
    table.appendChild(tbody);
    return table;
  }

  function fill(containerId, contentEl) {
    const el = document.getElementById(containerId);
    el.innerHTML = '';
    if (contentEl) el.appendChild(contentEl);
  }

  function toNumber(n) {
    if (typeof n === 'number') return n;
    if (typeof n === 'string') {
      const norm = n.replace(/\./g,'').replace(',','.');
      const parsed = parseFloat(norm);
      return isNaN(parsed) ? null : parsed;
    }
    return null;
  }

  function toNumberFlexible(n) {
    if (typeof n === 'number') return n;
    if (typeof n === 'string') {
      // Se tiver vírgula, assume formato pt-BR; senão, parse direto (ex: 430.00)
      if (n.includes(',')) {
        const norm = n.replace(/\./g,'').replace(',','.');
        const parsed = parseFloat(norm);
        return isNaN(parsed) ? null : parsed;
      }
      const parsed = parseFloat(n);
      return isNaN(parsed) ? null : parsed;
    }
    return null;
  }

  // Custos: remove bombeiro/seguranca/limpeza e corrige centavos
  function prepararCustos(custos) {
    const out = {};
    Object.entries(custos).forEach(([k, v]) => {
      const key = k.toLowerCase();
      if (key.includes('bombeiro') || key.includes('seguranca') || key.includes('segurança') || key.includes('limpeza')) {
        return;
      }
      let num = toNumber(v);
      if (num !== null) {
        if (key.includes('premontagem') || key.includes('operacional') || key.includes('total_operacional')) {
          num = num / 100; // corrige centavos
        }
        out[formatarTituloCusto(k)] = fmtBRL.format(num);
      } else {
        out[k] = v;
      }
    });
    return out;
  }

  function formatarTituloCusto(k) {
    const map = {
      'valor_premontagem': 'Pré-montagem',
      'premontagem': 'Pré-montagem',
      'valor_operacional': 'Operacional',
      'operacional': 'Operacional',
      'total_operacional': 'Total operacional'
    };
    return map[k.toLowerCase()] || k;
  }

  // Consumos: apenas totais, corrigindo centavos
  function prepararConsumos(cons) {
    const out = {};
    if (cons.diesel && cons.diesel.total) {
      const num = toNumber(cons.diesel.total);
      // Alguns totais chegam em centavos; padroniza para reais no modal.
      if (num !== null) out['Diesel total'] = fmtBRL.format(num/100);
    }
    if (cons.gerador_kwh) {
      const g1In = toNumberFlexible(cons.gerador_kwh.entrada_g1) || 0;
      const g1Out = toNumberFlexible(cons.gerador_kwh.saida_g1) || 0;
      const g2In = toNumberFlexible(cons.gerador_kwh.entrada_g2) || 0;
      const g2Out = toNumberFlexible(cons.gerador_kwh.saida_g2) || 0;
      const g1Cons = g1Out - g1In;
      const g2Cons = g2Out - g2In;
      const total = g1Cons + g2Cons;
      if (g1In || g1Out || g2In || g2Out) {
        const val = total.toFixed(0);
        out['Gerador Kwh'] = val + ' kWh';
      } else {
        out['Gerador Kwh'] = '0';
      }
    }
    if (cons.cedae && cons.cedae.total) {
      const num = toNumber(cons.cedae.total);
      if (num !== null) out['CEDAE total'] = fmtBRL.format(num/100);
    }
    if (cons.light && cons.light.total) {
      const num = toNumber(cons.light.total);
      if (num !== null) out['Light total'] = fmtBRL.format(num/100);
    }
    if (cons.poco && cons.poco.consumo !== undefined && cons.poco.consumo !== null) {
      const num = toNumberFlexible(cons.poco.consumo);
      if (num !== null) out['Poço consumo'] = num.toFixed(0) + ' m³';
    }
    return out;
  }

  function onEventClick(info) {
    info.jsEvent.preventDefault();
    const ev  = info.event;
    const ext = ev.extendedProps || {};

    // Cabeçalho e link Visualizar com nome
    const id  = ev.id;
    const url = "<?= site_url('update/operacional') ?>/" + id;
    const title = ev.title || `#${id}`;
    const modalIdEl = document.getElementById('modal-id');
    modalIdEl.textContent = '';
    const detailLink = document.createElement('a');
    detailLink.href = url;
    detailLink.textContent = title;
    modalIdEl.appendChild(detailLink);
    document.getElementById('modal-title').textContent = title;
    document.getElementById('modal-start').textContent = formatarData(ev.start);
    document.getElementById('modal-end').textContent   = ev.end ? formatarData(ev.end) : '---';
    document.getElementById('modal-type').textContent = ext.type || ext.tipo || '---';
    document.getElementById('modal-descricao').textContent = ext.descricao || '---';

    // Principais
    const principais = {
      'Data/Hora início': ev.start ? formatarData(ev.start) : '',
      'Data/Hora fim':    ev.end   ? formatarData(ev.end)   : '',
      'Hora extra':       ext.hora_extra,
      'Fornecedores':     ext.qtd_fornecedor
    };
    Object.keys(principais).forEach(k => { if (!principais[k]) delete principais[k]; });
    fill('conteudo-principais', Object.keys(principais).length ? renderTabela(principais) : textoVazio('Sem dados.'));

    // Custos
    const custosPrep = prepararCustos(ext.custos || {});
    fill('conteudo-custos', Object.keys(custosPrep).length ? renderTabela(custosPrep) : textoVazio('Sem dados de custos.'));

    // Consumos
    const consPrep = prepararConsumos(ext.consumos || {});
    fill('conteudo-consumos', Object.keys(consPrep).length ? renderTabela(consPrep) : textoVazio('Sem dados de consumo.'));

    // Público
    const publico = ext.publico || {};
    const publicoFlat = { 'Final': publico.final, 'Tribuna': publico.tribuna, 'Saída': publico.saida };
    Object.keys(publicoFlat).forEach(k => { if (!publicoFlat[k]) delete publicoFlat[k]; });
    fill('conteudo-publico', Object.keys(publicoFlat).length ? renderTabela(publicoFlat) : textoVazio('Sem dados de público.'));

    const modal = new bootstrap.Modal(document.getElementById('eventoModal'));
    modal.show();
    new bootstrap.Tab(document.querySelector('#tab-principais-tab')).show();
  }

  function textoVazio(msg) {
    const p = document.createElement('p');
    p.className = 'text-muted mb-0';
    p.textContent = msg;
    return p;
  }
});
</script>

<?php $this->endSection('content');?>
