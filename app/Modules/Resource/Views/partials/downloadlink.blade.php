

@foreach ($resources as $res)
    <form id="downloadForm-{{$res->id}}" action="{{ route('download.mailfile') }}" method="POST"  >
        @csrf
        <input type="hidden" name="slug" value="{{ $res->slug }}"> <!-- ID file -->
        <button type="submit"> <i class='feather icon-feather-download-cloud icon-extra-small  ' style='background:white'></i> nhận mail hướng dẫn về {{$res->title}} </button>
    </form>

   
@endforeach