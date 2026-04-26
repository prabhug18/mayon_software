@extends('layouts.backend')

@section('content')
   
    <div class="container-fluid my-3">
      <div class="row">
        <div class="col-md-3 col-sm-6">
          <div class="glass-card position-relative" data-accent="blue">
            <div class="icon-wrapper bg-primary">
              <i class="bi bi-clipboard-data"></i>
            </div>
            <h6 class="mt-5">Total Enquiries</h6>
            <p>{{ $enquiryCount ?? 0 }}</p>
          </div>
        </div>
        <div class="col-md-3 col-sm-6">
          <div id="today-followups-card" class="glass-card position-relative clickable" data-accent="green" role="button" title="Click to view details">
            <div class="icon-wrapper bg-success">
              <i class="bi bi-bell"></i>
            </div>
            <h6 class="mt-5">Today Follow-ups</h6>
            <p>{{ $todayFollowUpCount ?? 0 }}</p>
          </div>
        </div>
        <div class="col-md-3 col-sm-6">
          <div class="glass-card position-relative" data-accent="amber">
            <div class="icon-wrapper bg-amber">
              <i class="bi bi-calendar2-event"></i>
            </div>
            <h6 class="mt-5">Tomorrow Follow-ups</h6>
            <p>{{ $tomorrowFollowUpCount ?? 0 }}</p>
          </div>
        </div>
        <div class="col-md-3 col-sm-6">
          <div class="glass-card position-relative" data-accent="indigo">
            <div class="icon-wrapper bg-indigo">
              <i class="bi bi-file-earmark-text"></i>
            </div>
            <h6 class="mt-5">Total Quotations</h6>
            <p>{{ $quotationCount ?? 0 }}</p>
          </div>
        </div>
          <!-- Today Follow-ups Modal -->
          <div class="modal fade" id="todayFollowupsModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Today's Follow-ups</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <div id="today-followups-list">Loading…</div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                  <a href="{{ route('enquiries.index') }}" class="btn btn-primary">View All Enquiries</a>
                </div>
              </div>
            </div>
          </div>
        
      </div>
    </div>


   
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
  var range = document.getElementById('po-range');
  var from = document.getElementById('po-from');
  var to = document.getElementById('po-to');
  if (!range) return;
  range.addEventListener('change', function(){
    if (range.value === 'custom') { from.style.display = 'inline-block'; to.style.display = 'inline-block'; }
    else { from.style.display = 'none'; to.style.display = 'none'; }
  });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function(){
  var card = document.getElementById('today-followups-card');
  if (!card) return;
  card.addEventListener('click', function(){
    var modalEl = document.getElementById('todayFollowupsModal');
    var listEl = document.getElementById('today-followups-list');
    listEl.innerHTML = 'Loading…';
    fetch('{{ route('dashboard.followups.today') }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(function(res){ return res.json(); })
      .then(function(json){
        var data = json.data || [];
        if (!data.length) {
          listEl.innerHTML = '<div class="text-muted">No follow-ups scheduled for today.</div>';
        } else {
          var html = '<div class="table-responsive">';
          html += '<table class="table table-sm table-striped align-middle">';
          html += '<thead><tr><th>#</th><th>Name</th><th>Mobile</th><th>Type</th><th>Status</th><th>Source</th><th>Next Follow-up</th></tr></thead><tbody>';
          data.forEach(function(item, idx){
            html += '<tr>';
            html += '<td>' + (idx + 1) + '</td>';
            html += '<td><a href="/enquiries/' + item.id + '">' + (item.name || '-') + '</a></td>';
            html += '<td>' + (item.mobile || '-') + '</td>';
            html += '<td>' + (item.enquiry_type || '-') + '</td>';
            html += '<td>' + (item.status || '-') + '</td>';
            html += '<td>' + (item.source || '-') + '</td>';
            html += '<td>' + (item.next_follow_up_human || '-') + '</td>';
            html += '</tr>';
          });
          html += '</tbody></table></div>';
          listEl.innerHTML = html;
        }
        var modal = new bootstrap.Modal(modalEl);
        modal.show();
      })
      .catch(function(err){
        listEl.innerHTML = '<div class="text-danger">Failed to load follow-ups.</div>';
        var modal = new bootstrap.Modal(modalEl);
        modal.show();
      });
  });
});
</script>
@endpush
@push('styles')
<!-- Styling moved to public/assets/css/styles.css -->
@endpush
    