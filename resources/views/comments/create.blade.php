@extends('layouts.app')

@section('title', 'Create Comment')

@section('content')
    <div class="container">
        <h1>Create Comment</h1>

        <form action="{{ route('comments.store') }}" method="POST" class="mt-3">
            @csrf
            <div class="form-group mb-3">
                <label for="content">Content</label>
                <textarea class="form-control" name="content" id="content" rows="5">{{ old('content') }}</textarea>
                @error('content')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
        </form>
    </div>
@endsection
