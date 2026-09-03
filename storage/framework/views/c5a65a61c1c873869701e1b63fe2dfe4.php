        </div><!-- /.container-fluid -->
      </div><!-- /#content -->

      <?php if(! $modalView): ?>
      <!-- Footer -->
      <footer class="sticky-footer bg-white">
        <div class="container my-auto">
          <div class="copyright text-center my-auto">
            <span>&copy; <?php echo e(date('Y')); ?> <strong><a href="https://www.afrovertextours.com/">Afro Vertex Tours</a></strong>. All Rights Reserved</span>
          </div>
        </div>
      </footer>
      <?php endif; ?>
    </div><!-- /#content-wrapper -->
  </div><!-- /#wrapper -->

  <?php if(! $modalView): ?>
  <!-- Logout Modal -->
  <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="logoutModalLabel">Sign Out?</h5>
          <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">Are you sure you want to sign out?</div>
        <div class="modal-footer">
          <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
          <form method="POST" action="<?php echo e(route('logout')); ?>">
            <?php echo csrf_field(); ?>
            <button type="submit" class="btn btn-danger">Sign Out</button>
          </form>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <?php if(! $modalView): ?>
  <!-- Global Search Script -->
  <?php
      $user = auth()->user();
      $searchPages = [
          (object) ['name' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'fa-tachometer-alt', 'kw' => 'dashboard home', 'perm' => 'dashboard'],
          (object) ['name' => 'Static Pages', 'url' => route('admin.pages.index'), 'icon' => 'fa-file-alt', 'kw' => 'pages static', 'perm' => 'pages'],
          (object) ['name' => 'Tours & Packages', 'url' => route('admin.tour-packages.index'), 'icon' => 'fa-briefcase', 'kw' => 'tours packages trips', 'perm' => 'tours'],
          (object) ['name' => 'Tour Categories', 'url' => route('admin.tour-categories.index'), 'icon' => 'fa-tags', 'kw' => 'categories tour', 'perm' => 'tours'],
          (object) ['name' => 'User Management', 'url' => route('admin.users.index'), 'icon' => 'fa-users', 'kw' => 'users editors staff roles', 'perm' => 'users'],
          (object) ['name' => 'Blog Posts', 'url' => route('admin.blog-posts.index'), 'icon' => 'fa-newspaper', 'kw' => 'blog posts articles', 'perm' => 'blog'],
          (object) ['name' => 'Blog Categories', 'url' => route('admin.blog-categories.index'), 'icon' => 'fa-folder', 'kw' => 'blog categories', 'perm' => 'blog'],
          (object) ['name' => 'Translated Posts', 'url' => route('admin.translated-blogs.index'), 'icon' => 'fa-language', 'kw' => 'translated blogs', 'perm' => 'blog'],
          (object) ['name' => 'Destinations', 'url' => route('admin.destinations.index'), 'icon' => 'fa-map-marker-alt', 'kw' => 'destinations places locations', 'perm' => 'destinations'],
          (object) ['name' => 'Accommodations', 'url' => route('admin.accommodations.index'), 'icon' => 'fa-hotel', 'kw' => 'accommodations hotels lodging', 'perm' => 'accommodations'],
          (object) ['name' => 'Testimonials', 'url' => route('admin.testimonials.index'), 'icon' => 'fa-quote-right', 'kw' => 'testimonials reviews feedback', 'perm' => 'testimonials'],
          (object) ['name' => 'Media Library', 'url' => route('admin.media.index'), 'icon' => 'fa-images', 'kw' => 'media library photos images', 'perm' => 'media'],
          (object) ['name' => 'Compress Images', 'url' => route('admin.media.compression'), 'icon' => 'fa-compress-alt', 'kw' => 'compress images resize', 'perm' => 'media'],
          (object) ['name' => 'Bookings / Inquiries', 'url' => route('admin.inquiries.index'), 'icon' => 'fa-clipboard-list', 'kw' => 'bookings inquiries reservations', 'perm' => 'inquiries'],
          (object) ['name' => 'Settings', 'url' => route('admin.settings.index'), 'icon' => 'fa-cog', 'kw' => 'settings configuration system', 'perm' => 'settings'],
          (object) ['name' => 'Profile', 'url' => route('admin.profile.show'), 'icon' => 'fa-user', 'kw' => 'profile account password', 'perm' => 'profile'],
      ];
      $visiblePages = array_values(array_filter($searchPages, fn ($p) => $user->canAccess($p->perm)));
  ?>
  <script>
  (function(){
    var pages = <?php echo json_encode($visiblePages, 15, 512) ?>;
    var input = document.getElementById('globalSearch');
    var results = document.getElementById('globalSearchResults');
    if(!input) return;
    input.addEventListener('input', function(){
      var q = this.value.trim().toLowerCase();
      if(q.length < 1){ results.style.display='none'; results.innerHTML=''; return; }
      var matched = pages.filter(function(p){
        return p.name.toLowerCase().indexOf(q)!==-1 || p.kw.indexOf(q)!==-1;
      });
      if(matched.length===0){
        results.innerHTML = '<div class="text-center" style="padding:10px 14px; font-size:14px; color:var(--text-muted);"><i class="fas fa-search-minus mr-1"></i> No pages found</div>';
        results.style.display='block';
        return;
      }
      var html = '';
      matched.forEach(function(p){
        html += '<a href="'+p.url+'" class="d-flex align-items-center" style="padding:10px 14px; font-size:14px; text-decoration:none; color:var(--text); transition:background .2s;">'
          + '<i class="fas '+p.icon+' mr-2" style="color:var(--primary); width:18px; text-align:center;"></i> '
          + '<span>'+p.name+'</span></a>';
      });
      results.innerHTML = html;
      results.style.display = 'block';
    });
    input.addEventListener('focus', function(){ if(this.value.trim().length>=1) results.style.display='block'; });
    document.addEventListener('click', function(e){ if(!input.contains(e.target) && !results.contains(e.target)) results.style.display='none'; });
    input.addEventListener('keydown', function(e){
      if(e.key==='Enter'){
        var first = results.querySelector('a');
        if(first) window.location.href = first.getAttribute('href');
      }
      if(e.key==='Escape'){ results.style.display='none'; input.blur(); }
    });
  })();
  </script>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\tour\resources\views\admin\partials\footer.blade.php ENDPATH**/ ?>