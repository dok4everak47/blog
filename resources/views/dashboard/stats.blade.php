<x-app-layout>
    <x-slot name="header">
        <div class="max-w-6xl mx-auto">
            <p class="text-xs font-medium tracking-[0.2em] text-primary uppercase mb-1">Dashboard</p>
            <h2 class="font-bold text-xl text-text leading-tight">
                {{ __('浏览统计') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12 bg-bg">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            {{-- 概览卡片 --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="rounded-2xl border border-border bg-surface-2 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-text-secondary">总浏览量</p>
                            <p class="text-3xl font-bold text-text mt-1">{{ number_format($totalViews) }}</p>
                        </div>
                        <span class="text-3xl">👁️</span>
                    </div>
                </div>
                <div class="rounded-2xl border border-border bg-surface-2 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-text-secondary">今日浏览</p>
                            <p class="text-3xl font-bold text-text mt-1">{{ number_format($todayViews) }}</p>
                        </div>
                        <span class="text-3xl">📅</span>
                    </div>
                </div>
                <div class="rounded-2xl border border-border bg-surface-2 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-text-secondary">平均每日</p>
                            <p class="text-3xl font-bold text-text mt-1">
                                {{ $totalViews > 0 ? number_format($totalViews / max(1, $totalViews > 0 ? (int) ceil(PageView::whereNotNull('created_at')->count() > 0 ? PageView::selectRaw('DATEDIFF(MAX(created_at), MIN(created_at)) + 1 as days')->value('days') ?: 1 : 1) : 1)) : 0 }}
                            </p>
                        </div>
                        <span class="text-3xl">📊</span>
                    </div>
                </div>
            </div>

            {{-- 时间筛选 --}}
            <div class="flex items-center gap-2">
                <span class="text-sm text-text-secondary">时间范围：</span>
                @foreach ([7 => '7天', 14 => '14天', 30 => '30天', 90 => '90天'] as $d => $label)
                    <a href="{{ request()->fullUrlWithQuery(['days' => $d]) }}"
                        class="px-3 py-1.5 rounded-lg text-sm font-medium transition
                            {{ ($days = (int) request()->get('days', 30)) === $d ? 'bg-primary text-white' : 'bg-surface border border-border text-text hover:bg-surface-2' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            {{-- 趋势图（柱状图） --}}
            <div class="rounded-2xl border border-border bg-surface-2 p-6 sm:p-8">
                <p class="text-xs font-medium tracking-[0.2em] text-primary uppercase mb-4">浏览趋势</p>
                @php $dailyData = $dailyViews->toArray(); @endphp
                @if (!empty($dailyData))
                    <div x-data="{
                        data: {{ json_encode($dailyData) }},
                        get max() { return Math.max(...Object.values(this.data), 1); }
                    }">
                        <template x-for="(count, date) in data" :key="date">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="w-24 text-xs text-text-secondary shrink-0" x-text="date"></span>
                                <div class="flex-1 bg-gray-100 dark:bg-gray-800 rounded h-6 relative overflow-hidden">
                                    <div class="bg-blue-500 h-6 rounded flex items-center justify-end pr-2 text-xs text-white font-medium"
                                        :style="'width: ' + (count / max * 100) + '%'"
                                        x-text="count">
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                @else
                    <p class="text-sm text-text-secondary text-center py-6">暂无数据</p>
                @endif
            </div>

            {{-- 热门文章 --}}
            <div class="rounded-2xl border border-border bg-surface-2 p-6 sm:p-8">
                <p class="text-xs font-medium tracking-[0.2em] text-primary uppercase mb-4">热门文章 TOP 10</p>
                @if ($topNotes->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-text-secondary border-b border-border">
                                    <th class="pb-2 pr-4 w-8">#</th>
                                    <th class="pb-2 pr-4">标题</th>
                                    <th class="pb-2 text-right">浏览量</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($topNotes as $i => $item)
                                    <tr class="border-b border-border/50">
                                        <td class="py-2 pr-4 text-text-secondary">{{ $i + 1 }}</td>
                                        <td class="py-2 pr-4">
                                            @if ($item['note'])
                                                <a href="{{ route('notes.show', $item['note']) }}"
                                                    class="text-text hover:text-primary transition">
                                                    {{ $item['note']->title }}
                                                </a>
                                            @else
                                                <span class="text-text-muted">[已删除]</span>
                                            @endif
                                        </td>
                                        <td class="py-2 text-right font-medium">{{ number_format($item['views']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-sm text-text-secondary text-center py-6">暂无数据</p>
                @endif
            </div>

            {{-- 来源分析 --}}
            <div class="rounded-2xl border border-border bg-surface-2 p-6 sm:p-8">
                <p class="text-xs font-medium tracking-[0.2em] text-primary uppercase mb-4">来源分析 TOP 10</p>
                @php $totalReferrer = array_sum($topReferrers->toArray()) ?: 1; @endphp
                @if ($topReferrers->isNotEmpty())
                    <div class="space-y-2">
                        @foreach ($topReferrers as $domain => $count)
                            <div class="flex items-center gap-2">
                                <span class="w-40 text-sm text-text truncate shrink-0" title="{{ $domain }}">{{ $domain }}</span>
                                <div class="flex-1 bg-gray-100 dark:bg-gray-800 rounded h-6 relative overflow-hidden">
                                    <div class="bg-emerald-500 h-6 rounded"
                                        style="width: {{ $count / $totalReferrer * 100 }}%">
                                    </div>
                                </div>
                                <span class="w-16 text-xs text-text-secondary text-right shrink-0">{{ number_format($count) }}</span>
                                <span class="w-12 text-xs text-text-muted text-right shrink-0">
                                    {{ round($count / $totalReferrer * 100) }}%
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-text-secondary text-center py-6">暂无数据</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
