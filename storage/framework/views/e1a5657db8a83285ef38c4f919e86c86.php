
<?php $__env->startSection('title', 'Inquiry Details'); ?>

<?php $__env->startSection('content'); ?>
  <div class="pagetitle">
    <h1>
      Inquiry from <?php echo e($inquiry->name); ?>

      <?php if($inquiry->isTourBooking()): ?>
        <span class="badge bg-primary fs-14 align-middle">Tour Booking</span>
      <?php else: ?>
        <span class="badge bg-secondary fs-14 align-middle">Contact</span>
      <?php endif; ?>
    </h1>
    <nav>
      <ol class="breadcrumb">
        <li><a href="<?php echo e(route('admin.dashboard')); ?>">Home/</a></li>
        <li><a href="<?php echo e(route('admin.inquiries.index')); ?>">Inquiries/</a></li>
        <li class="breadcrumb-item active">Details</li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <div class="row">
      <div class="col-lg-8">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Inquiry Information</h5>

            <div class="row mb-3">
              <div class="col-sm-4 fw-bold">Name</div>
              <div class="col-sm-8"><?php echo e($inquiry->name); ?></div>
            </div>

            <div class="row mb-3">
              <div class="col-sm-4 fw-bold">Email</div>
              <div class="col-sm-8"><a href="mailto:<?php echo e($inquiry->email); ?>"><?php echo e($inquiry->email); ?></a></div>
            </div>

            <div class="row mb-3">
              <div class="col-sm-4 fw-bold">Phone / WhatsApp</div>
              <div class="col-sm-8"><?php echo e($inquiry->phone); ?></div>
            </div>

            <?php if($inquiry->country): ?>
              <div class="row mb-3">
                <div class="col-sm-4 fw-bold">Country</div>
                <div class="col-sm-8"><?php echo e($inquiry->country); ?></div>
              </div>
            <?php endif; ?>

            <div class="row mb-3">
              <div class="col-sm-4 fw-bold">Tour</div>
              <div class="col-sm-8">
                <?php if($inquiry->tour): ?>
                  <a href="<?php echo e(route('tour.show', $inquiry->tour->slug)); ?>" target="_blank">
                    <?php echo e($inquiry->tour->title); ?>

                  </a>
                <?php else: ?>
                  General Inquiry
                <?php endif; ?>
              </div>
            </div>

            <?php if($inquiry->isTourBooking()): ?>
              <hr>
              <h6 class="text-muted mb-3">Trip Preferences</h6>

              <?php if($inquiry->companions): ?>
                <div class="row mb-3">
                  <div class="col-sm-4 fw-bold">Travelling As</div>
                  <div class="col-sm-8"><?php echo e($inquiry->companions); ?></div>
                </div>
              <?php endif; ?>

              <?php if($inquiry->accommodation): ?>
                <div class="row mb-3">
                  <div class="col-sm-4 fw-bold">Accommodation Preference</div>
                  <div class="col-sm-8"><?php echo e($inquiry->accommodation); ?></div>
                </div>
              <?php endif; ?>

              <?php if($inquiry->room_type || $inquiry->bed_type): ?>
                <div class="row mb-3">
                  <div class="col-sm-4 fw-bold">Room / Bed Type</div>
                  <div class="col-sm-8"><?php echo e($inquiry->room_type ?? '-'); ?> / <?php echo e($inquiry->bed_type ?? '-'); ?></div>
                </div>
              <?php endif; ?>

              <?php if($inquiry->budget_min || $inquiry->budget_max): ?>
                <div class="row mb-3">
                  <div class="col-sm-4 fw-bold">Budget Range (per person)</div>
                  <div class="col-sm-8">
                    <?php echo e($inquiry->budget_min ? '$' . number_format($inquiry->budget_min, 0) : '-'); ?>

                    to
                    <?php echo e($inquiry->budget_max ? '$' . number_format($inquiry->budget_max, 0) : '-'); ?>

                  </div>
                </div>
              <?php endif; ?>

              <?php if($inquiry->adult_age_range || $inquiry->children_age_range): ?>
                <div class="row mb-3">
                  <div class="col-sm-4 fw-bold">Age Ranges</div>
                  <div class="col-sm-8">
                    Adults: <?php echo e($inquiry->adult_age_range ?? '-'); ?> | Children: <?php echo e($inquiry->children_age_range ?? 'None'); ?>

                  </div>
                </div>
              <?php endif; ?>
              <hr>
            <?php endif; ?>

            <div class="row mb-3">
              <div class="col-sm-4 fw-bold">Preferred Dates</div>
              <div class="col-sm-8">
                <?php echo e($inquiry->preferred_start_date ? $inquiry->preferred_start_date->format('d M Y') : '-'); ?>

                to
                <?php echo e($inquiry->preferred_end_date ? $inquiry->preferred_end_date->format('d M Y') : '-'); ?>

              </div>
            </div>

            <div class="row mb-3">
              <div class="col-sm-4 fw-bold">Group Size</div>
              <div class="col-sm-8"><?php echo e($inquiry->adults); ?> Adults, <?php echo e($inquiry->children); ?> Children</div>
            </div>

            <?php if($inquiry->total_amount): ?>
              <div class="row mb-3">
                <div class="col-sm-4 fw-bold">Total Amount</div>
                <div class="col-sm-8">$<?php echo e(number_format($inquiry->total_amount, 2)); ?></div>
              </div>
            <?php endif; ?>

            <?php if($inquiry->quote_snapshot): ?>
              <?php $snap = $inquiry->quote_snapshot; ?>
              <div class="row mb-3">
                <div class="col-sm-4 fw-bold">Price Quote</div>
                <div class="col-sm-8">
                  <?php if(($snap['request_type'] ?? '') === 'automatic'): ?>
                    $<?php echo e(number_format((float) $snap['price_pp'], 2)); ?> per person
                    (<?php echo e($snap['level_name'] ?? ucfirst(strtolower($snap['season'] ?? ''))); ?>,
                    <?php echo e(ucfirst(strtolower($snap['season_label'] ?? $snap['season'] ?? ''))); ?>)
                    &middot; group total: $<?php echo e(number_format((float) $snap['group_total'], 2)); ?>

                  <?php elseif(($snap['request_type'] ?? '') === 'custom'): ?>
                    <?php echo e($snap['note'] ?? 'Custom price requested.'); ?>

                  <?php else: ?>
                    Package price requested — no configured price for this tour.
                  <?php endif; ?>
                  <div class="small text-muted mt-1">
                    Server-generated quote for <?php echo e($snap['group_size'] ?? '-'); ?> traveler(s)
                    (<?php echo e($snap['season_label'] ?? 'High Season'); ?>). Currency: <?php echo e($snap['currency'] ?? 'USD'); ?>.
                  </div>
                </div>
              </div>
            <?php endif; ?>

            <div class="row mb-3">
              <div class="col-sm-4 fw-bold">Message</div>
              <div class="col-sm-8">
                <div class="p-3 bg-light rounded">
                  <?php echo nl2br(e($inquiry->message)); ?>

                </div>
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-sm-4 fw-bold">Submitted</div>
              <div class="col-sm-8"><?php echo e($inquiry->created_at->diffForHumans()); ?> (<?php echo e($inquiry->created_at->format('d M Y H:i')); ?>)</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right column - Status & Notes -->
      <div class="col-lg-4">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Manage Status</h5>

            <form action="<?php echo e(route('admin.inquiries.update', $inquiry)); ?>" method="POST">
              <?php echo csrf_field(); ?>
              <?php echo method_field('PUT'); ?>

              <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                  <option value="pending" <?php echo e($inquiry->status == 'pending' ? 'selected' : ''); ?>>Pending</option>
                  <option value="contacted" <?php echo e($inquiry->status == 'contacted' ? 'selected' : ''); ?>>Contacted</option>
                  <option value="confirmed" <?php echo e($inquiry->status == 'confirmed' ? 'selected' : ''); ?>>Confirmed</option>
                  <option value="cancelled" <?php echo e($inquiry->status == 'cancelled' ? 'selected' : ''); ?>>Cancelled</option>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label">Admin Notes</label>
                <textarea name="admin_notes" class="form-control" rows="5"><?php echo e(old('admin_notes', $inquiry->admin_notes)); ?></textarea>
              </div>

              <button type="submit" class="btn btn-primary">Update</button>
            </form>

            <hr>

            <form action="<?php echo e(route('admin.inquiries.destroy', $inquiry)); ?>" method="POST"
                  onsubmit="return confirm('Delete this inquiry? This cannot be undone.');">
              <?php echo csrf_field(); ?>
              <?php echo method_field('DELETE'); ?>
              <button type="submit" class="btn btn-outline-danger w-100">
                <i class="bi bi-trash"></i> Delete Inquiry
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\tour\resources\views\admin\inquiries\show.blade.php ENDPATH**/ ?>