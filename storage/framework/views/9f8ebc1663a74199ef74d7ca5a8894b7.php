
<?php $__env->startSection('title', 'Group Departures Calendar'); ?>

<?php $__env->startSection('content'); ?>
  <div class="pagetitle">
    <h1>Group Departures Calendar</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>">Home</a></li>
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.tour-packages.index')); ?>">Tour Packages</a></li>
        <li class="breadcrumb-item active">Group Departures Calendar</li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Upcoming Group Departures</h5>

            <!-- Calendar Container -->
            <div id="calendar"></div>
          </div>
        </div>
      </div>
    </div>
  </section>
<?php $__env->stopSection(); ?>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    var calendarEl = document.getElementById('calendar');

    var calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: 'dayGridMonth',           // default month view
      headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
      },
      events: <?php echo json_encode($events, 15, 512) ?>,                // Laravel passes PHP array → JSON
      eventClick: function(info) {
        if (info.event.url) {
          window.open(info.event.url, '_blank');  // open tour edit in new tab
          info.jsEvent.preventDefault();          // prevent default navigation
        }
      },
      eventMouseEnter: function(info) {
        // Tooltip with extra info
        let tooltip = new Tooltip(info.el, {
          title: `${info.event.extendedProps.spots}<br>Status: ${info.event.extendedProps.status}<br>Price: ${info.event.extendedProps.price}`,
          placement: 'top',
          trigger: 'hover',
          container: 'body',
          html: true,
          boundary: 'window'
        });
      },
      editable: false,                        // no drag/drop for now
      selectable: false,
      height: 'auto',
    });

    calendar.render();
  });
</script>

<!-- Bootstrap Tooltip (if not already included) -->
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\tour\resources\views\admin\group-departures\calendar.blade.php ENDPATH**/ ?>