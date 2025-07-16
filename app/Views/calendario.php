<?= $this->extend('main');?>
<?php $this->section('content');?>
 <!-- Div onde o calendário será inserido -->
 <div class="row">
    <div class="col-10" id='calendar'></div>
 </div>

    <!-- Modal Bootstrap -->
    <div class="modal" id="eventModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Modal body text goes here.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>


  <script>
document.addEventListener('DOMContentLoaded', () => {

  // ①  Captura os eventos vindos do PHP
  const events = <?= $fcEvents ?? '[]'; ?>;   // se não houver, devolve array vazio

  const calendar = new FullCalendar.Calendar(
      document.getElementById('calendar'),
      {
        initialView: 'dayGridMonth',
        locale: 'pt-br',

        // ②  Usa o array diretamente
        events: events,

        // events: [
        //             {
        //                 title: 'Evento teste',
        //                 start: '2025-05-14'
        //             },
        //             {
        //                 title: 'Evento 2',
        //                 start: '2025-05-09T12:00:00',
        //                 end: '2025-05-11T15:00:00'
        //             }
        //         ],
                eventClick: function(event) {
                  /* (a) Se estiver a usar **Bootstrap 5/‑6***/
                  document.getElementById('modalTitle').textContent = events[0].title;
                  const modalEl  = document.getElementById('eventModal');
                  const bsModal  = bootstrap.Modal.getOrCreateInstance(modalEl);
                  bsModal.show();
                },


        // headerToolbar: {
          // left: 'prev,next today',
          // center: 'title',
          // right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        // }
      }
  );

  calendar.render();
});
</script>

<?php $this->endSection('content');?>