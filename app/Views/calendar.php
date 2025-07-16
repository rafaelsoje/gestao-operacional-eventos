<?= $this->extend('main')?>;

<?php $this->section('content');?>
    <style>
        #calendar {
            max-width: 900px;
            margin: 40px auto;
        }
    </style>
</head>
<body>
    <!-- Div onde o calendário será inserido -->
    <div id='calendar'></div>

    <!-- Modal Bootstrap -->
    <div class="modal fade" id="eventModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Detalhes do Evento</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <p id="eventTitle"></p>
            <p id="eventDate"></p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
          </div>
        </div>
      </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#calendar').fullCalendar({
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'month,agendaWeek,agendaDay'
                },
                defaultDate: '2024-10-10',
                navLinks: true,
                editable: true,
                eventLimit: true,
                events: [
                    {
                        title: 'Evento teste',
                        start: '2025-05-10',
                        end: '2025-05-17'
                        
                    },
                    {
                        title: 'Evento 2',
                        start: '2024-10-12T14:00:00',
                        end: '2024-10-13T15:00:00'
                    }
                ],
                eventClick: function(event) {
                    // Passar os dados do evento para o modal
                    $('#eventTitle').text('Título: ' + event.title);
                    $('#eventDate').text('Data: ' + event.start.format("DD/MM/YYYY"));
                    
                    // Exibir o modal
                    $('#eventModal').modal('show');
                }
            });
        });
    </script>

<?php $this->endSection('content');?>