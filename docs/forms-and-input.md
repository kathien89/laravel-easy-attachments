## Forms and Input

Given a field named `avatar` like above...

In the view:

    {!! Form::open(['method'=>'post', 'files'=>true]) !!}
    {!! Form::file('avatar_image') !!}
    {!! Form::submit('Update') !!}
    {!! Form ::close() !!}

### In the controller

    function do_postback(Request $r)
    {
      $u = Auth::user();
      $u->update($r->input());
      $u->update($r->files()); // This is the important one
    }
