@if(count($errors) > 0)
    @foreach($errors->all() as $error)
        {{-- <div class="alert alert-danger session-message" style="color: #D8000C !important;background-color: #FFD2D2 !important;">
            {{$error}}
        </div> --}}
        <div class="alert alert-danger alert-dismissible fade show session-message" role="alert">
            {{$error}}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
    @endforeach
@endif

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show session-message" role="alert">
        {{session('success')}}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
@endif
    
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show session-message" role="alert">
        {{session('error')}}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
@endif

<script>
    $(function(){
        $('.session-message').delay(6000).fadeOut('slow');
    });
</script>