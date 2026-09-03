<?php if(!empty(session('success'))): ?>
<div class="alert alert-success" role="alert">
	<?php echo e(session('success')); ?>

</div>
<script>
  setTimeout(function () {
      location.reload(); // refresh page after 3 seconds
  }, 2000);
</script>
<?php endif; ?>

<?php if(!empty(session('error'))): ?>
<div class="alert alert-danger" role="alert">
	<?php echo e(session('error')); ?>

	<script>
      setTimeout(function () {
          location.reload(); // refresh page after 3 seconds
      }, 2000);
  </script>
</div>
<?php endif; ?><?php /**PATH C:\xampp\htdocs\tour\resources\views\admin\layouts\_message.blade.php ENDPATH**/ ?>