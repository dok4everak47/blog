{{ '<?xml version="1.0" encoding="UTF-8" ?>' }}
<rss version="2.0"
     xmlns:atom="http://www.w3.org/2005/Atom"
     xmlns:content="http://purl.org/rss/1.0/modules/content/">
    <channel>
        <title>{{ config('app.name', 'My Blog') }}</title>
        <link>{{ url('/') }}</link>
        <description>{{ config('app.name', 'My Blog') }} — 最新文章</description>
        <language>zh-CN</language>
        <copyright>{{ now()->year }} {{ config('app.name', 'My Blog') }}</copyright>
        <lastBuildDate>{{ $notes->first() ? $notes->first()->updated_at->toRssString() : now()->toRssString() }}</lastBuildDate>
        <atom:link href="{{ url('/feed.xml') }}" rel="self" type="application/rss+xml"/>

        @foreach($notes as $note)
        <item>
            <title>{{ $note->title }}</title>
            <link>{{ route('notes.show', $note) }}</link>
            <guid isPermaLink="true">{{ route('notes.show', $note) }}</guid>
            <description>{{ \Illuminate\Support\Str::limit(strip_tags($note->content ?? ''), 300) }}</description>
            @if($note->cover_image_url)
            <enclosure url="{{ url($note->cover_image_url) }}" type="image/jpeg"/>
            @endif
            <pubDate>{{ $note->created_at->toRssString() }}</pubDate>
            @if($note->category)
            <category>{{ $note->category->name }}</category>
            @endif
            @foreach($note->tags as $tag)
            <category>{{ $tag->name }}</category>
            @endforeach
        </item>
        @endforeach
    </channel>
</rss>
