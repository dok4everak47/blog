@extends('layouts.app')

@section('title', 'Comments')

@section('content')
    <div class="container">
        <h1>Comments</h1>
        <a href="{{ route('comments.create') }}" class="btn btn-primary mb-3">Create Comment</a>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Content</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($comments as $comment)
                    <tr>
                        <td>{{ $comment->id }}</td>
                        <td>{{ $comment->content }}</td>
                        <td>
                            <a href="{{ route('comments.show', $comment) }}">Show</a>
                            <a href="{{ route('comments.edit', $comment) }}">Edit</a>
                            <form action="{{ route('comments.destroy', $comment) }}" method="POST" style="display:inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
