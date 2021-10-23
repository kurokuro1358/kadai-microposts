@if (Auth::user()->is_favorite($micropost->id))
    {{-- お気に入りから外すフォーム --}}
    {!! Form::open(['route' => ['favorites.unfavorite', $micropost->id], 'method' =>'delete']) !!}
        {!! Form::submit('Unfavorite', ['class' => "btn btn-success btn-sm"]) !!}
    {!! Form::close() !!}
@else
    {{-- お気に入りにするフォーム --}}
    {!! Form::open(['route' => ['favorites.favorite', $micropost->id]]) !!}
        {!! Form::submit('Favorite', ['class' => "btn btn-light btn-sm"]) !!}
    {!! Form::close() !!}
@endif