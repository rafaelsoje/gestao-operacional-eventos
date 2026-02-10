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
#calendar-year-select {
  max-width:200px;
  margin: 1rem auto;
}

button{
  height: 50%;  
}
</style>

<div class="container py-4">
  <h2 class="text-center mb-3">Calendário de Propostas - <?= esc($ano) ?></h2>

  <!-- (Opcional) seletor de ano -->
  <div class="d-flex justify-content-center mb-4">
    <form method="get" action="" class="d-flex justify-content-center align-items-center gap-2" id="formAno">
      <input type="number" class="form-control" id="calendar-year-select" name="ano" value="<?= esc($ano) ?>" min="1970" max="2100">
      <button type="submit" class="btn btn-success">Pesquisar</button>
    </form>
  </div>

  <div class="calendar-grid">
    <?php
      // nomes dos meses PT-BR
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
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="eventoModalLabel">Detalhes da Proposta</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <p><strong>Título:</strong> <span id="modal-title"></span></p>
        <p><strong>Início:</strong> <span id="modal-start"></span></p>
        <p><strong>Fim:</strong> <span id="modal-end"></span></p>
        <p><strong>Status:</strong> <span id="modal-status" class="badge"></span></p>
        <p><strong>Tipo:</strong> <span id="modal-type"></span></p>
        <p><strong>Descrição:</strong> <span id="modal-descricao"></span></p>
        <p><strong>Visualizar proposta: </strong><span id="modal-id"></span></p>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const ano    = <?= (int)$ano ?>;
  const events = <?= $fcEvents ?? '[]' ?>; // todos os eventos do ano (ou mais)

  // Cria 12 calendários
  for (let mes=1; mes<=12; mes++) {
    const el = document.getElementById('cal-m'+mes);
    if (!el) continue;

    // --- rolar automaticamente até o mês corrente ---
  (function scrollToCurrentMonth() {
    const now = new Date();
    const currentYear  = now.getFullYear();
    const currentMonth = now.getMonth() + 1; // 1..12

    // só auto-rola se o ano exibido for o ano atual
    if (ano !== currentYear) return;

    const target = document.getElementById('cal-m' + currentMonth);
    if (!target) return;

    // se tiver header fixo, ajuste este offset
    const HEADER_OFFSET = 80; // px (ajuste se necessário)

    requestAnimationFrame(() => {
      const y = target.getBoundingClientRect().top + window.pageYOffset - HEADER_OFFSET;
      window.scrollTo({ top: y, behavior: 'smooth' });
    });
  })();

    // filtra eventos para este mês/ano (melhora legibilidade/performance)
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
      eventClick: onEventClick
    });

    cal.render();
  }

  // --- Funções auxiliares ---

  function filtrarEventosPorMes(all, year, month) {
    // month: 1-12
    const ini = new Date(Date.UTC(year, month-1, 1, 0,0,0));
    const end = new Date(Date.UTC(year, month, 0, 23,59,59)); // último dia

    return all.filter(ev => {
      // parse datas; assume formato "YYYY-MM-DD HH:MM:SS" ou ISO
      const start = toDateUTC(ev.start);
      const evEnd = ev.end ? toDateUTC(ev.end) : start;
      // se intervalo do evento toca o mês
      return (evEnd >= ini && start <= end);
    });
  }

  function toDateUTC(str) {
    // tenta parse simples; se já é ISO, Date resolve; se "YYYY-MM-DD HH:MM:SS"
    if (typeof str !== 'string') return new Date(str);
    // normaliza espaço -> T
    const iso = str.replace(' ', 'T');
    return new Date(iso+'Z'); // força UTC; remova 'Z' se suas datas já têm tz
  }

  function onEventClick(info) {
    info.jsEvent.preventDefault();
    const ev   = info.event;
    const ext  = ev.extendedProps || {};

    // Preenche modal
    const id = ev.id;
    const url = `update/proposta/${id}`;

    document.getElementById('modal-id').innerHTML = `<a href="${url}"> #${ev.title}</a>`;
    // document.getElementById('modal-id').textContent = ev.id;
    document.getElementById('modal-title').textContent = ev.title;
    document.getElementById('modal-start').textContent = formatarData(ev.start);
    document.getElementById('modal-end').textContent   = ev.end ? formatarData(ev.end) : '---';
    const statusTxt = traduzStatus(ext.status);
    const badgeEl = document.getElementById('modal-status');
    badgeEl.textContent = statusTxt;
    aplicarCorStatusBadge(badgeEl, ext.status);
    document.getElementById('modal-type').textContent = ext.type || '---';
    document.getElementById('modal-descricao').textContent = ext.descricao || '---';

    // Mostra modal
    const modal = new bootstrap.Modal(document.getElementById('eventoModal'));
    modal.show();
  }

  function traduzStatus(status) {
    switch (String(status)) {
      case '1': return 'Proposta Aceita';
      case '2': return 'Proposta em Análise';
      case '3': return 'Proposta Rejeitada';
      default:  return 'Desconhecido';
    }
  }

  function aplicarCorStatusBadge(el, status) {
    el.classList.remove('bg-success','bg-warning','bg-danger','bg-secondary');
    switch (String(status)) {
      case '1': el.classList.add('bg-success'); break;
      case '2': el.classList.add('bg-warning'); el.style.color='#000'; break; // warning = amarelo
      case '3': el.classList.add('bg-danger');  break;
      default:  el.classList.add('bg-secondary');
    }
  }

  function formatarData(dateObj) {
    // cuidado: FullCalendar fornece Date já ajustado ao fuso local
    return dateObj.toLocaleString('pt-BR');
  }
});
</script>
<?php $this->endSection('content');?>
