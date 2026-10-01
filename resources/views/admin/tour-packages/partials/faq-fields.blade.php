@php
    $faqFields = old('faqs', [['question' => '', 'answer' => '']]);
    if (!is_array($faqFields) || $faqFields === []) {
        $faqFields = [['question' => '', 'answer' => '']];
    }
@endphp
<div class="row mb-4 mt-4">
  <label class="col-sm-2 col-form-label"><strong>FAQs</strong></label>
  <div class="col-sm-10">
    <div id="faqs-wrapper">
      @foreach($faqFields as $faqIndex => $faq)
        <div class="faq-item row mb-3 align-items-start">
          <div class="col-md-5"><input type="text" name="faqs[{{ $faqIndex }}][question]" class="form-control" value="{{ $faq['question'] ?? '' }}" placeholder="Question"></div>
          <div class="col-md-5"><textarea name="faqs[{{ $faqIndex }}][answer]" class="form-control" rows="3" placeholder="Answer">{{ $faq['answer'] ?? '' }}</textarea></div>
          <div class="col-md-2"><button type="button" class="btn btn-sm btn-outline-danger remove-faq">Remove</button></div>
        </div>
      @endforeach
    </div>
    <button type="button" id="add-faq" class="btn btn-sm btn-outline-primary mt-2"><i class="bi bi-plus-circle"></i> Add FAQ</button>
  </div>
</div>
@push('scripts')
<script>
(function(){
  var wrapper=document.getElementById('faqs-wrapper');
  var addButton=document.getElementById('add-faq');
  if(!wrapper||!addButton)return;
  var nextIndex=Array.from(wrapper.querySelectorAll('[name^="faqs["]')).reduce(function(max,input){var match=input.name.match(/^faqs\[(\d+)\]/);return match?Math.max(max,Number(match[1])+1):max;},0);
  addButton.addEventListener('click',function(){
    var item=document.createElement('div');
    item.className='faq-item row mb-3 align-items-start';
    item.innerHTML='<div class="col-md-5"><input type="text" class="form-control" placeholder="Question" name="faqs['+nextIndex+'][question]"></div><div class="col-md-5"><textarea class="form-control" rows="3" placeholder="Answer" name="faqs['+nextIndex+'][answer]"></textarea></div><div class="col-md-2"><button type="button" class="btn btn-sm btn-outline-danger remove-faq">Remove</button></div>';
    wrapper.appendChild(item);
    nextIndex++;
  });
  wrapper.addEventListener('click',function(event){
    var button=event.target.closest('.remove-faq');
    if(button)button.closest('.faq-item').remove();
  });
})();
</script>
@endpush
