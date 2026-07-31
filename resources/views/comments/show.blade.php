@extends('layouts.app')

@section('title', 'Comment Details')

@section('content')
    <div class="container">
        <h1>Comment</h1>

        <dl class="mt-3">
            <dt>ID</dt>
            <dd>{{ $comment->id }}</dd>
            <dt>Content</dt>
            <dd>{{ $comment->content }}</dd>
        </dl>

        <a href="{{ route('comments.edit', $comment) }}" class="btn btn-warning">Edit</a>
        <a href="{{ route('comments.index') }}" class="btn btn-secondary">Back</a>
    </div>
@endsection
