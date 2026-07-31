@extends('layouts.app')

@section('title', 'Edit Comment')

@section('content')
    <div class="container">
        <h1>Edit Comment</h1>

        <form action="{{ route('comments.update', $comment) }}" method="POST" class="mt-3">
            @csrf
            @method('PUT')
            <div class="form-group mb-3">
                <label for="content">Content</label>
                <textarea class="form-control" name="content" id="content" rows="5">{{ old('content', $comment->content) }}</textarea>
                @error('content')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
@endsection
