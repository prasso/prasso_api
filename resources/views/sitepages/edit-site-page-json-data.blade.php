@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Site Page JSON Data</h1>
    <form action="{{ route('sitepages.update-site-page-json-data', ['siteId' => $siteId, 'sitePageId' => $sitePageId]) }}" method="POST">
        @csrf
        @method('PUT')
        @foreach ($sitePageData as $data)
        <div class="form-group">
            <label for="data_{{ $data->id }}">Data Item {{ $loop->index + 1 }}</label>
            <textarea class="form-control" id="data_{{ $data->id }}" name="data[{{ $data->id }}]">{{ $data->json_data }}</textarea>
            <a href="{{ route('sitepages.delete-site-page-json-data', ['siteId' => $siteId, 'sitePageId' => $sitePageId, 'dataId' => $data->id]) }}" class="btn btn-danger">Delete</a>
        </div>
        @endforeach
        <button type="submit" class="btn btn-primary">Save Changes</button>
    </form>
</div>
@endsection
