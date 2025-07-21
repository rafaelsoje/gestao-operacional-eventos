<?= $this->extend('main');?>
<?php $this->section('content');?>

<div class="container mt-5">
    <h1 class="text-center mb-4">Calendário de Propostas</h1>
    <div id="calendar"></div>
</div>

<!-- Modal -->
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
        <p><strong>Tipo:</strong> <span id="modal-type"></span></p>
        <p><strong>Status:</strong> <span id="modal-status"></span></p>
        <p><strong>Descrição:</strong> <span id="modal-descricao"></span></p>
      </div>
    </div>
  </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const calendarEl = document.getElementById('calendar');

        const events = <?= $fcEvents ?? '[]'; ?>;

        const calendar = new FullCalendar.Calendar(calendarEl, {
            locale: 'pt-br',
            initialView: 'dayGridMonth',
            height: 'auto',
            // headerToolbar: {
            //     left: 'prev,next today',
            //     center: 'title',
            //     right: 'dayGridMonth,timeGridWeek,timeGridDay'
            // },
            events: events,
            eventClick: function(info) {
                const event = info.event;
                const props = event.extendedProps;

                document.getElementById('modal-title').textContent = event.title;
                document.getElementById('modal-start').textContent = event.start.toLocaleString('pt-BR');
                document.getElementById('modal-end').textContent = event.end ? event.end.toLocaleString('pt-BR') : '---';
                document.getElementById('modal-status').textContent = formatarStatus(props.status);
                document.getElementById('modal-type').textContent = props.type;
                document.getElementById('modal-descricao').textContent = props.descricao || '---';

                const modal = new bootstrap.Modal(document.getElementById('eventoModal'));
                modal.show();
            }
        });

        calendar.render();

        function formatarStatus(status) {
            switch (status) {
                case '1': return 'Proposta Aceita';
                case '2': return 'Proposta em análise';
                case '3': return 'Proposta Rejeitada';
                default: return 'Desconhecido';
            }
        }
    });
</script>

<?php $this->endSection('content');?>