@extends('layouts.app')

@section('title', 'スタッフ一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin_staff_list.css') }}">
@endsection

@section('content')
<div class="staff-list">
    <div class="staff-list__inner">
        <h2 class="staff-list__heading">
            <span class="staff-list__bar"></span>
            スタッフ一覧
        </h2>

        <div class="staff-list__table-wrapper">
            <table class="staff-list__table">
                <thead>
                    <tr>
                        <th>名前</th>
                        <th>メールアドレス</th>
                        <th>月次勤怠</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td class="staff-list__detail">
                            <a href="{{ url('/admin/attendance/staff/' . $user->id) }}">詳細</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="empty">スタッフがいません</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->lastPage() > 1)
        <div class="pagination">
            @if ($users->onFirstPage())
            <span class="pagination__item pagination__item--disabled">‹</span>
            @else
            <a class="pagination__item" href="{{ $users->previousPageUrl() }}">‹</a>
            @endif

            @for ($page = 1; $page <= $users->lastPage(); $page++)
                @if ($page == $users->currentPage())
                <span class="pagination__item pagination__item--active">{{ $page }}</span>
                @else
                <a class="pagination__item" href="{{ $users->url($page) }}">{{ $page }}</a>
                @endif
                @endfor

                @if ($users->hasMorePages())
                <a class="pagination__item" href="{{ $users->nextPageUrl() }}">›</a>
                @else
                <span class="pagination__item pagination__item--disabled">›</span>
                @endif
        </div>
        @endif

    </div>
</div>
@endsection
