@extends('layouts.app')

@section('title', '申請一覧（管理者）')

@section('css')
<link rel="stylesheet" href="{{ asset('css/stamp_correction_request_list.css') }}">
@endsection

@section('content')
@php
$tab = request('tab', 'pending');
@endphp

<div class="request-list">
    <div class="request-list__inner">

        <h1 class="request-list__heading">
            <span class="request-list__bar"></span>
            申請一覧
        </h1>

        <div class="request-list__tabs">
            <a class="tab {{ $tab === 'pending' ? 'tab--active' : '' }}"
                href="/admin/stamp_correction_request/list?tab=pending">
                承認待ち
            </a>

            <a class="tab {{ $tab === 'approved' ? 'tab--active' : '' }}"
                href="/admin/stamp_correction_request/list?tab=approved">
                承認済み
            </a>
        </div>

        <div class="request-list__table-wrapper">
            <table class="request-list__table">
                <thead>
                    <tr>
                        <th>状態</th>
                        <th>名前</th>
                        <th>対象日付</th>
                        <th>申請理由</th>
                        <th>申請日時</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @if($tab === 'pending')
                    @forelse ($pendingRequests as $r)
                    <tr>
                        <td>承認待ち</td>
                        <td>{{ $r->user->name }}</td>
                        <td>{{ \Carbon\Carbon::parse($r->attendance->date)->format('Y/m/d') }}</td>
                        <td>{{ $r->requested_note }}</td>
                        <td>{{ \Carbon\Carbon::parse($r->created_at)->format('Y/m/d') }}</td>
                        <td class="detail">
                            <a href="/stamp_correction_request/approve/{{ $r->id }}">詳細</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="empty">申請はありません</td>
                    </tr>
                    @endforelse
                    @else
                    @forelse ($approvedRequests as $r)
                    <tr>
                        <td>承認済み</td>
                        <td>{{ $r->user->name }}</td>
                        <td>{{ \Carbon\Carbon::parse($r->attendance->date)->format('Y/m/d') }}</td>
                        <td>{{ $r->requested_note }}</td>
                        <td>{{ \Carbon\Carbon::parse($r->created_at)->format('Y/m/d') }}</td>
                        <td class="detail">
                            <a href="/stamp_correction_request/approve/{{ $r->id }}">詳細</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="empty">承認済みの申請はありません</td>
                    </tr>
                    @endforelse
                    @endif
                </tbody>
            </table>
        </div>

        @php
        $paginator = $tab === 'pending' ? $pendingRequests : $approvedRequests;
        $pageName = $tab === 'pending' ? 'pending_page' : 'approved_page';
        @endphp

        @if ($paginator->lastPage() > 1)
        <div class="pagination">
            @if ($paginator->onFirstPage())
            <span class="pagination__item pagination__item--disabled">‹</span>
            @else
            <a class="pagination__item" href="{{ $paginator->appends(['tab' => $tab])->previousPageUrl() }}">‹</a>
            @endif

            @for ($page = 1; $page <= $paginator->lastPage(); $page++)
                @if ($page == $paginator->currentPage())
                <span class="pagination__item pagination__item--active">{{ $page }}</span>
                @else
                <a class="pagination__item"
                    href="{{ $paginator->appends(['tab' => $tab, $pageName => $page])->url($page) }}">
                    {{ $page }}
                </a>
                @endif
                @endfor

                @if ($paginator->hasMorePages())
                <a class="pagination__item" href="{{ $paginator->appends(['tab' => $tab])->nextPageUrl() }}">›</a>
                @else
                <span class="pagination__item pagination__item--disabled">›</span>
                @endif
        </div>
        @endif

    </div>
</div>
@endsection