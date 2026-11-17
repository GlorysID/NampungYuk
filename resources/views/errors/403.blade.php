@extends('errors.minimal')

@section('code', '403')
@section('title', 'Akses Ditolak')
@section('message', $exception->getMessage() ?: 'Kamu tidak memiliki izin untuk mengakses halaman atau tindakan ini.')
