@extends('layouts.admin')

@section('title', 'Buat Form Checklist')
@section('page-title', 'Form Builder')

@section('content')
<div class="form-builder-container">
    <form method="POST" action="{{ route('admin.forms.store') }}" id="formBuilder" class="form-builder-form">
        @csrf

        <!-- Header Section -->
        <div class="builder-header">
            <div class="builder-header-content">
                <div>
                    <h1 class="builder-title">Buat Form Checklist Baru</h1>
                    <p class="builder-subtitle">Atur konfigurasi form dan tambahkan item checklist yang diperlukan</p>
                </div>
                <div class="builder-actions">
                    <a href="{{ route('admin.forms.index') }}" class="btn btn-outline">
                        <span class="icon-left">←</span>
                        Kembali
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="builder-main">
            <!-- Left Panel: Form Configuration -->
            <div class="builder-panel configuration-panel">
                <div class="panel-section">
                    <div class="section-header">
                        <h2 class="section-title">
                            <i class="fas fa-sliders-h icon-section" aria-hidden="true"></i>
                            Konfigurasi Form
                        </h2>
                    </div>

                    <!-- Informasi Dasar -->
                    <div class="config-group">
                        <h3 class="group-title">Informasi Dasar</h3>
                        
                        <div class="form-field">
                            <label for="title" class="form-label">
                                Nama Form
                                <span class="required">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="title"
                                name="title" 
                                class="form-input @error('title') is-error @enderror"
                                value="{{ old('title') }}" 
                                placeholder="Contoh: Checklist Access Point Lt.2"
                                required
                            >
                            @error('title')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-field">
                            <label for="description" class="form-label">
                                Deskripsi / Tujuan
                                <span class="optional">(opsional)</span>
                            </label>
                            <textarea 
                                id="description"
                                name="description" 
                                class="form-textarea" 
                                rows="3"
                                placeholder="Jelaskan tujuan dan konteks form ini..."
                            >{{ old('description') }}</textarea>
                        </div>
                    </div>

                    <!-- Jadwal & Periode -->
                    <div class="config-group">
                        <h3 class="group-title">Jadwal & Periode</h3>

                        <div class="form-field">
                            <label for="scheduleType" class="form-label">
                                Tipe Jadwal
                                <span class="required">*</span>
                            </label>
                            <select 
                                id="scheduleType"
                                name="schedule_type"
                                class="form-select @error('schedule_type') is-error @enderror"
                                required
                            >
                                <option value="daily" {{ old('schedule_type') == 'daily' ? 'selected' : '' }}>
                                    Harian
                                </option>
                                <option value="weekly" {{ old('schedule_type') == 'weekly' ? 'selected' : '' }}>
                                    Mingguan
                                </option>
                                <option value="custom" {{ old('schedule_type') == 'custom' ? 'selected' : '' }}>
                                    Kustom
                                </option>
                            </select>
                            @error('schedule_type')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Pilihan Hari (untuk Weekly) -->
                        <div id="scheduleDaysContainer" class="form-field" style="display: none;">
                            <label class="form-label">Hari</label>
                            <div class="days-grid">
                                @php
                                $days = ['Mon'=>'Senin','Tue'=>'Selasa','Wed'=>'Rabu','Thu'=>'Kamis','Fri'=>'Jumat','Sat'=>'Sabtu','Sun'=>'Minggu'];
                                @endphp
                                @foreach($days as $val => $label)
                                <label class="checkbox-item">
                                    <input 
                                        type="checkbox" 
                                        name="schedule_days[]" 
                                        value="{{ $val }}"
                                        {{ (is_array(old('schedule_days')) && in_array($val, old('schedule_days'))) ? 'checked' : '' }}
                                    >
                                    <span class="checkbox-label">{{ $label }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Interval Kustom -->
                        <div id="customIntervalContainer" class="form-field" style="display: none;">
                            <label for="scheduleInterval" class="form-label">
                                Interval
                                <span class="hint">(dalam hari)</span>
                            </label>
                            <input 
                                type="number" 
                                id="scheduleInterval"
                                name="schedule_interval" 
                                class="form-input" 
                                value="{{ old('schedule_interval', 1) }}" 
                                min="1"
                            >
                        </div>

                        <!-- Tanggal Mulai & Berakhir -->
                        <div class="form-row">
                            <div class="form-field">
                                <label for="startDate" class="form-label">
                                    Tanggal Mulai
                                    <span class="required">*</span>
                                </label>
                                <input 
                                    type="date" 
                                    id="startDate"
                                    name="start_date" 
                                    class="form-input" 
                                    value="{{ old('start_date') }}"
                                >
                            </div>
                            <div class="form-field">
                                <label for="endDate" class="form-label">
                                    Tanggal Berakhir
                                    <span class="optional">(opsional)</span>
                                </label>
                                <input 
                                    type="date" 
                                    id="endDate"
                                    name="end_date" 
                                    class="form-input" 
                                    value="{{ old('end_date') }}"
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Penugasan User -->
                    <div class="config-group">
                        <h3 class="group-title">Penugasan</h3>

                        <div class="form-field">
                            <label for="assignedUsers" class="form-label">
                                Assign ke User
                                <span class="optional">(opsional)</span>
                            </label>
                            <select 
                                id="assignedUsers"
                                name="assigned_users[]" 
                                class="form-select-multi"
                                multiple
                                size="5"
                            >
                                @foreach($users as $u)
                                <option 
                                    value="{{ $u->id }}"
                                    {{ (is_array(old('assigned_users')) && in_array($u->id, old('assigned_users'))) ? 'selected' : '' }}
                                >
                                    {{ $u->name }} ({{ $u->email }})
                                </option>
                                @endforeach
                            </select>
                            <span class="form-hint"><i class="fas fa-info-circle" aria-hidden="true"></i> Gunakan Ctrl+Click atau Cmd+Click untuk memilih beberapa user</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Panel: Items Builder -->
            <div class="builder-panel checklist-panel">
                <div class="panel-section">
                    <div class="section-header">
                        <h2 class="section-title">
                            <i class="fas fa-list-check icon-section" aria-hidden="true"></i>
                            Item Checklist
                            <span class="item-count" id="itemCountBadge">(0)</span>
                        </h2>
                        <button 
                            type="button" 
                            class="btn btn-primary btn-sm"
                            onclick="addItem()"
                        >
                            <i class="fas fa-plus icon-left" aria-hidden="true"></i>
                            Tambah Item
                        </button>
                    </div>

                    <div class="items-builder">
                        <div id="itemsContainer" class="items-list">
                            <!-- Items akan ditambahkan di sini -->
                        </div>

                        <div id="emptyState" class="empty-state">
                            <div class="empty-state-icon"><i class="fas fa-clipboard-list" aria-hidden="true"></i></div>
                            <h4 class="empty-state-title">Belum ada item</h4>
                            <p class="empty-state-text">Mulai tambahkan item checklist dengan mengklik tombol "Tambah Item"</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Actions -->
        <div class="builder-footer">
            <div class="footer-content">
                <button 
                    type="submit" 
                    class="btn btn-primary btn-lg"
                    id="publishBtn"
                >
                    <i class="fas fa-paper-plane icon-left" aria-hidden="true"></i>
                    Publish Form
                </button>
                <a href="{{ route('admin.forms.index') }}" class="btn btn-secondary btn-lg">
                    Batal
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Item Template Modal (tidak terlihat) -->
<template id="itemTemplate">
    <div class="item-card" draggable="true">
        <div class="item-header">
            <div class="item-identifier">
                <span class="item-number">#</span>
            </div>
            <input type="hidden" class="item-order-index" name="items[idx][order_index]" value="">
            <div class="item-actions">
                <button type="button" class="btn-icon drag-handle" title="Geser untuk mengubah urutan">
                    <i class="fas fa-grip-vertical" aria-hidden="true"></i>
                </button>
                <button type="button" class="btn-icon" onclick="duplicateItem(this)" title="Duplikasi item">
                    <i class="far fa-copy" aria-hidden="true"></i>
                </button>
                <button type="button" class="btn-icon btn-danger" onclick="removeItem(this)" title="Hapus item">
                    <i class="fas fa-trash-alt" aria-hidden="true"></i>
                </button>
            </div>
        </div>

        <div class="item-body">
            <div class="form-row">
                <div class="form-field flex-1">
                    <label class="form-label">Label <span class="required">*</span></label>
                    <input 
                        type="text" 
                        class="form-input item-label" 
                        placeholder="Contoh: IP Address, Status Server, dll"
                        required
                    >
                </div>
                <div class="form-field flex-0">
                    <label class="form-label">Tipe Field <span class="required">*</span></label>
                    <select class="form-select item-type" required>
                        <option value="text">Text Input</option>
                        <option value="number">Number</option>
                        <option value="textarea">Textarea</option>
                        <option value="checkbox">Checkbox</option>
                        <option value="radio">Radio (Ya/Tidak/NA)</option>
                        <option value="dropdown">Dropdown</option>
                        <option value="signal">Signal Strength</option>
                        <option value="photo">Upload Foto</option>
                    </select>
                </div>
                <div class="form-field flex-0">
                    <label class="form-label">&nbsp;</label>
                    <label class="checkbox-item">
                        <input type="checkbox" class="item-required">
                        <span class="checkbox-label">Wajib</span>
                    </label>
                </div>
            </div>

            <div class="item-options" style="display: none;">
                <label class="form-label">Opsi <span class="hint">(pisahkan dengan koma)</span></label>
                <input 
                    type="text" 
                    class="form-input item-options-input" 
                    placeholder="Online, Offline, Maintenance"
                >
            </div>

            <div class="form-row">
                <div class="form-field flex-1">
                    <label class="form-label">Placeholder</label>
                    <input 
                        type="text" 
                        class="form-input item-placeholder" 
                        placeholder="Contoh: Masukkan nilai di sini"
                    >
                </div>
                <div class="form-field flex-1">
                    <label class="form-label">Helper Text</label>
                    <input 
                        type="text" 
                        class="form-input item-helper" 
                        placeholder="Bantuan untuk user"
                    >
                </div>
            </div>
        </div>
    </div>
</template>

<style>
:root {
    --color-primary: #2563eb;
    --color-primary-light: #3b82f6;
    --color-primary-dark: #1e40af;
    --color-success: #16a34a;
    --color-danger: #dc2626;
    --color-warning: #ea580c;
    --color-neutral-50: #f9fafb;
    --color-neutral-100: #f3f4f6;
    --color-neutral-200: #e5e7eb;
    --color-neutral-300: #d1d5db;
    --color-neutral-400: #9ca3af;
    --color-neutral-500: #6b7280;
    --color-neutral-600: #4b5563;
    --color-neutral-700: #374151;
    --color-neutral-800: #1f2937;
    --color-neutral-900: #111827;

    --spacing-2: 0.5rem;
    --spacing-3: 0.75rem;
    --spacing-4: 1rem;
    --spacing-5: 1.25rem;
    --spacing-6: 1.5rem;
    --spacing-8: 2rem;
    --spacing-10: 2.5rem;
    --spacing-12: 3rem;

    --radius-sm: 0.375rem;
    --radius-md: 0.5rem;
    --radius-lg: 0.75rem;

    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);

    --font-size-xs: 0.75rem;
    --font-size-sm: 0.875rem;
    --font-size-base: 1rem;
    --font-size-lg: 1.125rem;
    --font-size-xl: 1.25rem;
    --font-size-2xl: 1.5rem;
}

* {
    box-sizing: border-box;
}

.form-builder-container {
    min-height: 100vh;
    background: linear-gradient(135deg, var(--color-neutral-50) 0%, #ffffff 100%);
}

/* Header */
.builder-header {
    background: white;
    border-bottom: 1px solid var(--color-neutral-200);
    padding: var(--spacing-8);
    position: sticky;
    top: 0;
    z-index: 10;
    box-shadow: var(--shadow-sm);
}

.builder-header-content {
    max-width: 1600px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.builder-title {
    margin: 0 0 var(--spacing-2) 0;
    font-size: var(--font-size-2xl);
    font-weight: 700;
    color: var(--color-neutral-900);
}

.builder-subtitle {
    margin: 0;
    font-size: var(--font-size-sm);
    color: var(--color-neutral-500);
}

.builder-actions {
    display: flex;
    gap: var(--spacing-3);
}

/* Main Layout */
.builder-main {
    max-width: 1600px;
    margin: 0 auto;
    padding: var(--spacing-8);
    display: grid;
    grid-template-columns: 1fr;
    gap: var(--spacing-8);
}

.builder-panel {
    background: white;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    overflow: hidden;
}

.panel-section {
    padding: var(--spacing-8);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-6);
    padding-bottom: var(--spacing-6);
    border-bottom: 2px solid var(--color-neutral-100);
}

.section-title {
    margin: 0;
    font-size: var(--font-size-lg);
    font-weight: 600;
    color: var(--color-neutral-900);
    display: flex;
    align-items: center;
    gap: var(--spacing-3);
}

.icon-section {
    font-size: 1.25rem;
}

.item-count {
    font-size: var(--font-size-sm);
    color: var(--color-neutral-500);
    font-weight: 400;
}

/* Configuration Groups */
.config-group {
    margin-bottom: var(--spacing-8);
}

.config-group:last-child {
    margin-bottom: 0;
}

.group-title {
    margin: 0 0 var(--spacing-5) 0;
    font-size: var(--font-size-base);
    font-weight: 600;
    color: var(--color-neutral-700);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Form Elements */
.form-field {
    margin-bottom: var(--spacing-5);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-4);
}

.form-label {
    display: block;
    margin-bottom: var(--spacing-2);
    font-size: var(--font-size-sm);
    font-weight: 600;
    color: var(--color-neutral-700);
}

.required {
    color: var(--color-danger);
}

.optional {
    font-size: var(--font-size-xs);
    font-weight: 400;
    color: var(--color-neutral-500);
}

.hint {
    font-size: var(--font-size-xs);
    font-weight: 400;
    color: var(--color-neutral-500);
}

.form-input,
.form-textarea,
.form-select,
.form-select-multi {
    width: 100%;
    padding: var(--spacing-3) var(--spacing-4);
    font-size: var(--font-size-base);
    border: 1px solid var(--color-neutral-300);
    border-radius: var(--radius-md);
    background: white;
    color: var(--color-neutral-900);
    transition: all 0.2s ease;
}

.form-input:focus,
.form-textarea:focus,
.form-select:focus,
.form-select-multi:focus {
    outline: none;
    border-color: var(--color-primary);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.form-input.is-error,
.form-textarea.is-error,
.form-select.is-error {
    border-color: var(--color-danger);
}

.form-textarea {
    font-family: inherit;
    resize: vertical;
    min-height: 80px;
}

.form-error {
    display: block;
    margin-top: var(--spacing-2);
    font-size: var(--font-size-sm);
    color: var(--color-danger);
}

.form-hint {
    display: block;
    margin-top: var(--spacing-2);
    font-size: var(--font-size-sm);
    color: var(--color-neutral-500);
}

/* Days Grid */
.days-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
    gap: var(--spacing-3);
}

.checkbox-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    cursor: pointer;
    padding: var(--spacing-3);
    border-radius: var(--radius-md);
    border: 1px solid var(--color-neutral-200);
    transition: all 0.2s ease;
}

.checkbox-item:hover {
    border-color: var(--color-primary);
    background: var(--color-neutral-50);
}

.checkbox-item input {
    cursor: pointer;
    accent-color: var(--color-primary);
}

.checkbox-label {
    font-size: var(--font-size-sm);
    color: var(--color-neutral-700);
    font-weight: 500;
}

/* Items Builder */
.items-builder {
    min-height: 400px;
}

.items-list {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-5);
}

.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 400px;
    padding: var(--spacing-8);
    text-align: center;
}

.empty-state-icon {
    font-size: 3rem;
    margin-bottom: var(--spacing-4);
    opacity: 0.5;
}

.empty-state-title {
    margin: 0 0 var(--spacing-2) 0;
    font-size: var(--font-size-base);
    font-weight: 600;
    color: var(--color-neutral-700);
}

.empty-state-text {
    margin: 0;
    font-size: var(--font-size-sm);
    color: var(--color-neutral-500);
    max-width: 280px;
}

/* Item Card */
.item-card {
    background: var(--color-neutral-50);
    border: 2px solid var(--color-neutral-200);
    border-radius: var(--radius-lg);
    padding: var(--spacing-5);
    transition: all 0.2s ease;
    cursor: grab;
}

.item-card:hover {
    border-color: var(--color-primary);
    box-shadow: var(--shadow-md);
}

.item-card.dragging {
    opacity: 0.5;
    background: var(--color-primary-light);
    border-color: var(--color-primary);
}

.item-card.drag-over {
    border-color: var(--color-primary);
    background: rgba(37, 99, 235, 0.05);
}

.item-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-4);
    padding-bottom: var(--spacing-4);
    border-bottom: 1px solid var(--color-neutral-300);
}

.item-identifier {
    display: flex;
    align-items: center;
    gap: var(--spacing-3);
}

.item-number {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    background: var(--color-primary);
    color: white;
    border-radius: var(--radius-md);
    font-size: var(--font-size-sm);
    font-weight: 600;
}

.item-actions {
    display: flex;
    gap: var(--spacing-2);
}

.btn-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    background: white;
    border: 1px solid var(--color-neutral-300);
    border-radius: var(--radius-md);
    cursor: pointer;
    font-size: var(--font-size-base);
    transition: all 0.2s ease;
}

.btn-icon:hover {
    border-color: var(--color-primary);
    background: rgba(37, 99, 235, 0.05);
}

.btn-icon.btn-danger:hover {
    border-color: var(--color-danger);
    background: rgba(220, 38, 38, 0.05);
}

.drag-handle {
    cursor: grab;
}

.drag-handle:active {
    cursor: grabbing;
}

.item-body {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-4);
}

.item-options {
    padding: var(--spacing-3) var(--spacing-4);
    background: white;
    border-radius: var(--radius-md);
    border-left: 3px solid var(--color-warning);
}

/* Flex Utilities */
.flex-1 {
    flex: 1;
}

.flex-0 {
    flex: 0;
    min-width: 140px;
}

/* Buttons */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: var(--spacing-2);
    padding: var(--spacing-3) var(--spacing-4);
    font-size: var(--font-size-sm);
    font-weight: 600;
    border: none;
    border-radius: var(--radius-md);
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
}

.btn-primary {
    background: var(--color-primary);
    color: white;
}

.btn-primary:hover {
    background: var(--color-primary-dark);
    box-shadow: var(--shadow-md);
}

.btn-secondary {
    background: var(--color-neutral-100);
    color: var(--color-neutral-700);
    border: 1px solid var(--color-neutral-300);
}

.btn-secondary:hover {
    background: var(--color-neutral-200);
}

.btn-outline {
    background: transparent;
    color: var(--color-neutral-700);
    border: 1px solid var(--color-neutral-300);
}

.btn-outline:hover {
    background: var(--color-neutral-50);
    border-color: var(--color-primary);
}

.btn-sm {
    padding: var(--spacing-2) var(--spacing-3);
    font-size: var(--font-size-xs);
}

.btn-lg {
    padding: var(--spacing-4) var(--spacing-6);
    font-size: var(--font-size-base);
}

.icon-left {
    font-size: var(--font-size-lg);
}

/* Footer */
.builder-footer {
    background: white;
    border-top: 1px solid var(--color-neutral-200);
    padding: var(--spacing-8);
    position: sticky;
    bottom: 0;
    z-index: 10;
    box-shadow: 0 -4px 6px -1px rgba(0, 0, 0, 0.1);
}

.footer-content {
    max-width: 1600px;
    margin: 0 auto;
    display: flex;
    gap: var(--spacing-4);
}

/* Responsive */
@media (max-width: 1024px) {
    .builder-main {
        grid-template-columns: 1fr;
    }

    .form-row {
        grid-template-columns: 1fr;
    }

    .flex-0 {
        flex: 1;
        min-width: unset;
    }
}

@media (max-width: 768px) {
    .builder-header {
        padding: var(--spacing-5);
    }

    .builder-header-content {
        flex-direction: column;
        gap: var(--spacing-4);
        align-items: flex-start;
    }

    .builder-main {
        padding: var(--spacing-5);
        gap: var(--spacing-5);
    }

    .panel-section {
        padding: var(--spacing-5);
    }

    .builder-footer {
        padding: var(--spacing-5);
    }

    .footer-content {
        flex-direction: column;
    }

    .footer-content .btn {
        width: 100%;
    }

    .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: var(--spacing-3);
    }

    .item-header {
        flex-direction: column;
        align-items: flex-start;
        gap: var(--spacing-3);
    }

    .item-actions {
        width: 100%;
        justify-content: flex-start;
    }

    .days-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 480px) {
    .builder-title {
        font-size: var(--font-size-xl);
    }

    .btn {
        padding: var(--spacing-3) var(--spacing-3);
    }

    .item-card {
        padding: var(--spacing-4);
    }
}
</style>

<script>
let itemIndex = 0;
let draggedItem = null;

const itemTemplate = document.getElementById('itemTemplate');
const itemsContainer = document.getElementById('itemsContainer');
const scheduleType = document.getElementById('scheduleType');

// Field type options mapping
const fieldTypes = {
    text: 'Text Input',
    number: 'Number',
    textarea: 'Textarea',
    checkbox: 'Checkbox',
    radio: 'Radio (Ya/Tidak/NA)',
    dropdown: 'Dropdown',
    signal: 'Signal Strength',
    photo: 'Upload Foto'
};

const optionFields = ['checkbox', 'radio', 'dropdown', 'signal'];

function addItem(data = null) {
    const idx = itemIndex++;
    const clone = itemTemplate.content.cloneNode(true);

    // Update all name attributes dan data-index
    const card = clone.querySelector('.item-card');
    card.dataset.itemIndex = idx;

    // Update form field names
    const updateName = (selector, template) => {
        const el = clone.querySelector(selector);
        if (el) el.name = template.replace('idx', idx);
    };

    updateName('.item-order-index', 'items[idx][order_index]');

    const labelInput = clone.querySelector('.item-label');
    labelInput.name = `items[${idx}][label]`;
    labelInput.value = data?.label || '';

    const typeSelect = clone.querySelector('.item-type');
    typeSelect.name = `items[${idx}][field_type]`;
    typeSelect.value = data?.field_type || 'text';
    typeSelect.addEventListener('change', function() {
        updateOptionsVisibility(this.closest('.item-card'));
    });

    const requiredCheck = clone.querySelector('.item-required');
    requiredCheck.name = `items[${idx}][is_required]`;
    requiredCheck.checked = data?.is_required || false;

    const optionsInput = clone.querySelector('.item-options-input');
    optionsInput.name = `items[${idx}][options_raw]`;
    optionsInput.value = data?.options_raw || '';

    const placeholderInput = clone.querySelector('.item-placeholder');
    placeholderInput.name = `items[${idx}][placeholder]`;
    placeholderInput.value = data?.placeholder || '';

    const helperInput = clone.querySelector('.item-helper');
    helperInput.name = `items[${idx}][helper_text]`;
    helperInput.value = data?.helper_text || '';

    // Set item number
    const itemNumber = clone.querySelector('.item-number');
    itemNumber.textContent = `#${idx + 1}`;

    itemsContainer.appendChild(clone);
    const newCard = itemsContainer.querySelector(`[data-item-index="${idx}"]`);
    
    initItemCard(newCard);
    updateOptionsVisibility(newCard);
    updateEmptyState();
}

function initItemCard(card) {
    card.addEventListener('dragstart', handleDragStart);
    card.addEventListener('dragend', handleDragEnd);
    card.addEventListener('dragover', handleDragOver);
    card.addEventListener('drop', handleDrop);
}

function handleDragStart(e) {
    draggedItem = this;
    this.classList.add('dragging');
    e.dataTransfer.effectAllowed = 'move';
}

function handleDragEnd(e) {
    this.classList.remove('dragging');
    document.querySelectorAll('.item-card').forEach(card => {
        card.classList.remove('drag-over');
    });
}

function handleDragOver(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';

    if (draggedItem === this) return;

    this.classList.add('drag-over');

    const allCards = [...itemsContainer.querySelectorAll('.item-card')];
    const draggedIdx = allCards.indexOf(draggedItem);
    const targetIdx = allCards.indexOf(this);

    if (draggedIdx < targetIdx) {
        this.parentNode.insertBefore(draggedItem, this.nextSibling);
    } else {
        this.parentNode.insertBefore(draggedItem, this);
    }
}

function handleDrop(e) {
    e.preventDefault();
    this.classList.remove('drag-over');
    refreshItemOrder();
}

function duplicateItem(btn) {
    const card = btn.closest('.item-card');
    const data = readItemData(card);
    addItem(data);
    refreshItemOrder();
}

function removeItem(btn) {
    btn.closest('.item-card').remove();
    refreshItemOrder();
    updateEmptyState();
}

function readItemData(card) {
    return {
        label: card.querySelector('.item-label').value,
        field_type: card.querySelector('.item-type').value,
        is_required: card.querySelector('.item-required').checked,
        options_raw: card.querySelector('.item-options-input').value,
        placeholder: card.querySelector('.item-placeholder').value,
        helper_text: card.querySelector('.item-helper').value,
    };
}

function updateOptionsVisibility(card) {
    const type = card.querySelector('.item-type').value;
    const optionsRow = card.querySelector('.item-options');
    optionsRow.style.display = optionFields.includes(type) ? 'block' : 'none';
}

function refreshItemOrder() {
    document.querySelectorAll('#itemsContainer .item-card').forEach((card, index) => {
        card.querySelector('.item-number').textContent = `#${index + 1}`;
        card.querySelector('.item-order-index').value = index;
    });
    updateItemCount();
}

function updateEmptyState() {
    const hasItems = itemsContainer.querySelectorAll('.item-card').length > 0;
    document.getElementById('emptyState').style.display = hasItems ? 'none' : 'flex';
}

function updateItemCount() {
    const count = itemsContainer.querySelectorAll('.item-card').length;
    document.getElementById('itemCountBadge').textContent = `(${count})`;
}

// Schedule type toggle
scheduleType.addEventListener('change', function() {
    document.getElementById('scheduleDaysContainer').style.display = 
        this.value === 'weekly' ? 'block' : 'none';
    document.getElementById('customIntervalContainer').style.display = 
        this.value === 'custom' ? 'block' : 'none';
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    const oldSchedule = scheduleType.value;
    document.getElementById('scheduleDaysContainer').style.display = 
        oldSchedule === 'weekly' ? 'block' : 'none';
    document.getElementById('customIntervalContainer').style.display = 
        oldSchedule === 'custom' ? 'block' : 'none';

    // Load old items if any
    const oldItems = @json(old('items', []));
    if (oldItems.length > 0) {
        oldItems.forEach(item => addItem(item));
    } else {
        addItem();
    }

    updateEmptyState();
});
</script>

@endsection