@if(!empty(session('success')))
<div class="alert alert-success" role="alert">
	{{ session('success')}}
</div>
<script>
  setTimeout(function () {
      location.reload(); // refresh page after 3 seconds
  }, 2000);
</script>
@endif

@if(!empty(session('error')))
<div class="alert alert-danger" role="alert">
	{{ session('error')}}
	<script>
      setTimeout(function () {
          location.reload(); // refresh page after 3 seconds
      }, 2000);
  </script>
</div>
@endif