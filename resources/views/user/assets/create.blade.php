@extends('layouts.user')

@section('title', 'Tambah Asset')
@section('page-title', 'Tambah Asset')

@section('content')
<div class="card shadow-sm border-0"><div class="card-body"><form method="POST" action="{{ route('user.assets.store') }}">@include('admin.assets._form', ['submitLabel' => 'Simpan Asset', 'cancelRoute' => route('user.assets.index')])</form></div></div>
@endsection