@extends('layout')

@section('content')
  <h1>Files</h1>
  <ul>
    @foreach($files as $f)
      <li>{{$f->original_file_name}}
    @endforeach
  </ul>
@endsection
