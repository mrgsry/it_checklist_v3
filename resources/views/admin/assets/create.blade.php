@extends('layouts.admin')

@section('title', 'Tambah Asset')
@section('page-title', 'Tambah Asset')

@section('content')
<div class="card shadow-sm border-0"><div class="card-body"><form method="POST" action="{{ route('admin.assets.store') }}">@include('admin.assets._form', ['submitLabel' => 'Simpan Asset'])</form></div></div>
@endsection