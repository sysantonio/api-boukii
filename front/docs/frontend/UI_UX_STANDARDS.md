# 🎨 Estándares UI/UX - Boukii V5

> **Objetivo:** Mantener consistencia visual, usabilidad y profesionalismo en todas las pantallas del sistema.

---

## 📋 Índice

1. [Principios de Diseño](#principios)
2. [Sistema de Colores](#colores)
3. [Tipografía](#tipografia)
4. [Componentes y Patrones](#componentes)
5. [Layout y Spacing](#layout)
6. [Estados de Interfaz](#estados)
7. [Responsive Design](#responsive)
8. [Accesibilidad](#accesibilidad)
9. [Micro-interacciones](#microinteracciones)
10. [Guías por Tipo de Pantalla](#guias-pantalla)

---

## 🎯 Principios de Diseño {#principios}

### 1. **Clarity First (Claridad Primero)**
- **Información jerarquizada**: Lo más importante se ve primero
- **Acciones obvias**: Botones principales claramente identificables  
- **Feedback inmediato**: Usuario siempre sabe qué está pasando
- **Lenguaje claro**: Textos simples y directos

### 2. **Consistency (Consistencia)**
- **Componentes reutilizables**: Mismo elemento, mismo comportamiento
- **Patrones predecibles**: Usuario aprende una vez, aplica en todo el sistema
- **Design tokens**: Colores, espaciado y tipografía estandarizados

### 3. **Efficiency (Eficiencia)**
- **Flujos rápidos**: Mínimo número de clics para tareas comunes
- **Shortcuts**: Atajos de teclado para usuarios avanzados
- **Bulk actions**: Operaciones masivas donde sea útil
- **Smart defaults**: Valores predeterminados inteligentes

### 4. **Progressive Disclosure (Revelación Progresiva)**
- **Información básica visible**: Detalles bajo demanda
- **Configuración avanzada**: En secciones expandibles o modales
- **Onboarding gradual**: Usuario aprende el sistema paso a paso

---

## 🎨 Sistema de Colores {#colores}

### Paleta Principal
```css
/* Brand Colors */
--brand-50: #eff6ff;    /* Backgrounds claros */
--brand-100: #dbeafe;   /* Hover states suaves */
--brand-200: #bfdbfe;   /* Borders activos */
--brand-300: #93c5fd;   /* Disabled states */
--brand-400: #60a5fa;   /* Secondary actions */
--brand-500: #3b82f6;   /* Primary actions */
--brand-600: #2563eb;   /* Primary hover */
--brand-700: #1d4ed8;   /* Primary active */
--brand-800: #1e40af;   /* Dark theme primary */
--brand-900: #1e3a8a;   /* Dark theme hover */
```

### Colores Semánticos
```css
/* Success */
--success-50: #f0fdf4;
--success-500: #22c55e;
--success-700: #15803d;

/* Warning */
--warning-50: #fffbeb;
--warning-500: #f59e0b;
--warning-700: #a16207;

/* Error */
--error-50: #fef2f2;
--error-500: #ef4444;
--error-700: #c53030;

/* Info */
--info-50: #f0f9ff;
--info-500: #0ea5e9;
--info-700: #0369a1;
```

### Neutrales
```css
/* Gray Scale */
--gray-50: #f9fafb;     /* Page backgrounds */
--gray-100: #f3f4f6;    /* Component backgrounds */
--gray-200: #e5e7eb;    /* Borders */
--gray-300: #d1d5db;    /* Placeholders */
--gray-400: #9ca3af;    /* Secondary text */
--gray-500: #6b7280;    /* Muted text */
--gray-600: #4b5563;    /* Primary text */
--gray-700: #374151;    /* Dark text */
--gray-800: #1f2937;    /* Dark theme backgrounds */
--gray-900: #111827;    /* Dark theme text */
```

### Reglas de Uso
```scss
// ✅ Buenas prácticas
.primary-button {
  background-color: var(--brand-500);
  border: 1px solid var(--brand-500);
  color: white;
  
  &:hover {
    background-color: var(--brand-600);
    border-color: var(--brand-600);
  }
  
  &:disabled {
    background-color: var(--gray-300);
    border-color: var(--gray-300);
    color: var(--gray-500);
  }
}

// ❌ Evitar
.bad-button {
  background-color: #3b82f6; /* Hard-coded color */
  color: #ffffff; /* No design token */
}
```

---

## 📝 Tipografía {#tipografia}

### Jerarquía Tipográfica
```css
/* Font Sizes */
--text-xs: 12px;        /* Captions, small labels */
--text-sm: 14px;        /* Body text, form inputs */
--text-base: 16px;      /* Default body text */
--text-lg: 18px;        /* Emphasized text */
--text-xl: 20px;        /* Small headings */
--text-2xl: 24px;       /* Section headings */
--text-3xl: 30px;       /* Page headings */
--text-4xl: 36px;       /* Hero headings */

/* Font Weights */
--font-normal: 400;     /* Regular text */
--font-medium: 500;     /* Emphasized text */
--font-semibold: 600;   /* Section headings */
--font-bold: 700;       /* Page headings */

/* Line Heights */
--leading-tight: 1.25;  /* Headings */
--leading-normal: 1.5;  /* Body text */
--leading-relaxed: 1.75; /* Long form content */
```

### Aplicación por Componente
```scss
// Page Headings
.page-title {
  font-size: var(--text-3xl);
  font-weight: var(--font-bold);
  line-height: var(--leading-tight);
  color: var(--gray-900);
  margin-bottom: var(--space-4);
}

// Section Headings
.section-title {
  font-size: var(--text-xl);
  font-weight: var(--font-semibold);
  line-height: var(--leading-tight);
  color: var(--gray-800);
  margin-bottom: var(--space-3);
}

// Body Text
.body-text {
  font-size: var(--text-base);
  font-weight: var(--font-normal);
  line-height: var(--leading-normal);
  color: var(--gray-700);
}

// Small Text
.small-text {
  font-size: var(--text-sm);
  font-weight: var(--font-normal);
  line-height: var(--leading-normal);
  color: var(--gray-500);
}
```

---

## 🧩 Componentes y Patrones {#componentes}

### Anatomía de Botones
```scss
/* Base Button */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: var(--space-2);
  padding: var(--space-3) var(--space-4);
  border-radius: var(--radius-md);
  font-size: var(--text-sm);
  font-weight: var(--font-medium);
  line-height: 1;
  border: 1px solid transparent;
  cursor: pointer;
  transition: all 150ms ease;
  outline: none;
  text-decoration: none;

  /* Focus state */
  &:focus-visible {
    box-shadow: 0 0 0 3px var(--brand-200);
  }

  /* Disabled state */
  &:disabled {
    cursor: not-allowed;
    opacity: 0.6;
  }
}

/* Primary Button */
.btn--primary {
  background-color: var(--brand-500);
  border-color: var(--brand-500);
  color: white;

  &:hover:not(:disabled) {
    background-color: var(--brand-600);
    border-color: var(--brand-600);
  }

  &:active:not(:disabled) {
    background-color: var(--brand-700);
    border-color: var(--brand-700);
  }
}

/* Secondary Button */
.btn--secondary {
  background-color: white;
  border-color: var(--gray-300);
  color: var(--gray-700);

  &:hover:not(:disabled) {
    background-color: var(--gray-50);
    border-color: var(--gray-400);
  }
}

/* Button Sizes */
.btn--sm {
  padding: var(--space-2) var(--space-3);
  font-size: var(--text-xs);
}

.btn--lg {
  padding: var(--space-4) var(--space-6);
  font-size: var(--text-base);
}
```

### Anatomía de Form Inputs
```scss
/* Base Input */
.input {
  width: 100%;
  padding: var(--space-3);
  border: 1px solid var(--gray-300);
  border-radius: var(--radius-md);
  background-color: white;
  font-size: var(--text-sm);
  line-height: var(--leading-normal);
  color: var(--gray-900);
  transition: border-color 150ms ease, box-shadow 150ms ease;

  &::placeholder {
    color: var(--gray-400);
  }

  &:focus {
    outline: none;
    border-color: var(--brand-500);
    box-shadow: 0 0 0 3px var(--brand-100);
  }

  &:disabled {
    background-color: var(--gray-100);
    color: var(--gray-500);
    cursor: not-allowed;
  }

  &.is-invalid {
    border-color: var(--error-500);

    &:focus {
      border-color: var(--error-500);
      box-shadow: 0 0 0 3px var(--error-100);
    }
  }
}

/* Form Field Container */
.form-field {
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
  margin-bottom: var(--space-4);

  .field-label {
    font-size: var(--text-sm);
    font-weight: var(--font-medium);
    color: var(--gray-700);

    &.required::after {
      content: ' *';
      color: var(--error-500);
    }
  }

  .field-error {
    font-size: var(--text-xs);
    color: var(--error-600);
    margin-top: var(--space-1);
  }

  .field-hint {
    font-size: var(--text-xs);
    color: var(--gray-500);
  }
}
```

### Cards y Containers
```scss
/* Base Card */
.card {
  background-color: white;
  border: 1px solid var(--gray-200);
  border-radius: var(--radius-lg);
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
  overflow: hidden;

  .card-header {
    padding: var(--space-6);
    border-bottom: 1px solid var(--gray-200);
    background-color: var(--gray-50);

    .card-title {
      font-size: var(--text-lg);
      font-weight: var(--font-semibold);
      color: var(--gray-900);
      margin: 0;
    }

    .card-subtitle {
      font-size: var(--text-sm);
      color: var(--gray-500);
      margin: var(--space-1) 0 0 0;
    }
  }

  .card-content {
    padding: var(--space-6);
  }

  .card-footer {
    padding: var(--space-4) var(--space-6);
    border-top: 1px solid var(--gray-200);
    background-color: var(--gray-50);
  }
}
```

---

## 📐 Layout y Spacing {#layout}

### Sistema de Spacing
```css
/* Spacing Scale */
--space-0: 0px;
--space-px: 1px;
--space-1: 4px;
--space-2: 8px;
--space-3: 12px;
--space-4: 16px;
--space-5: 20px;
--space-6: 24px;
--space-8: 32px;
--space-10: 40px;
--space-12: 48px;
--space-16: 64px;
--space-20: 80px;
--space-24: 96px;
```

### Reglas de Spacing
```scss
/* Vertical Rhythm */
.page-content {
  > * + * {
    margin-top: var(--space-6); /* Consistent vertical spacing */
  }

  .section-title {
    margin-top: var(--space-12); /* Extra space before sections */
    margin-bottom: var(--space-4);
  }
}

/* Component Internal Spacing */
.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: var(--space-4); /* Consistent gap between form fields */
}

.button-group {
  display: flex;
  gap: var(--space-3); /* Consistent gap between buttons */
  
  &.vertical {
    flex-direction: column;
    gap: var(--space-2);
  }
}
```

### Page Layout Patterns
```scss
/* Standard Page Layout */
.page {
  max-width: 1200px;
  margin: 0 auto;
  padding: var(--space-6) var(--space-8);

  .page-header {
    margin-bottom: var(--space-8);
    
    .page-title {
      margin-bottom: var(--space-2);
    }
    
    .page-actions {
      margin-top: var(--space-4);
    }
  }

  .page-content {
    margin-bottom: var(--space-8);
  }
}

/* Two Column Layout */
.two-column-layout {
  display: grid;
  grid-template-columns: 1fr 320px;
  gap: var(--space-8);

  .main-content {
    min-width: 0; /* Prevent overflow */
  }

  .sidebar {
    .sidebar-section + .sidebar-section {
      margin-top: var(--space-6);
    }
  }
}
```

---

## 🔄 Estados de Interfaz {#estados}

### Loading States
```scss
/* Skeleton Loading */
.skeleton {
  background: linear-gradient(
    90deg,
    var(--gray-200) 25%,
    var(--gray-100) 50%,
    var(--gray-200) 75%
  );
  background-size: 200% 100%;
  animation: loading 1.5s infinite;
  border-radius: var(--radius-md);
}

@keyframes loading {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

/* Spinner Loading */
.spinner {
  width: 20px;
  height: 20px;
  border: 2px solid var(--gray-200);
  border-top: 2px solid var(--brand-500);
  border-radius: 50%;
  animation: spin 1s linear infinite;

  &--large {
    width: 32px;
    height: 32px;
    border-width: 3px;
  }
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Button Loading State */
.btn.is-loading {
  position: relative;
  color: transparent;

  &::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 16px;
    height: 16px;
    border: 2px solid currentColor;
    border-top: 2px solid transparent;
    border-radius: 50%;
    animation: spin 1s linear infinite;
  }
}
```

### Empty States
```scss
.empty-state {
  text-align: center;
  padding: var(--space-12) var(--space-6);

  .empty-icon {
    width: 64px;
    height: 64px;
    margin: 0 auto var(--space-4);
    color: var(--gray-400);
  }

  .empty-title {
    font-size: var(--text-lg);
    font-weight: var(--font-semibold);
    color: var(--gray-900);
    margin-bottom: var(--space-2);
  }

  .empty-description {
    font-size: var(--text-sm);
    color: var(--gray-500);
    margin-bottom: var(--space-6);
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
  }

  .empty-action {
    /* Primary button styles */
  }
}
```

### Error States
```scss
.error-state {
  padding: var(--space-6);
  border: 1px solid var(--error-200);
  border-radius: var(--radius-lg);
  background-color: var(--error-50);

  .error-icon {
    width: 20px;
    height: 20px;
    color: var(--error-500);
    margin-bottom: var(--space-3);
  }

  .error-title {
    font-size: var(--text-base);
    font-weight: var(--font-semibold);
    color: var(--error-800);
    margin-bottom: var(--space-2);
  }

  .error-message {
    font-size: var(--text-sm);
    color: var(--error-700);
    margin-bottom: var(--space-4);
  }

  .error-actions {
    display: flex;
    gap: var(--space-3);
  }
}
```

### Success States
```scss
.success-message {
  display: flex;
  align-items: center;
  gap: var(--space-3);
  padding: var(--space-4);
  border: 1px solid var(--success-200);
  border-radius: var(--radius-md);
  background-color: var(--success-50);
  color: var(--success-800);

  .success-icon {
    width: 20px;
    height: 20px;
    color: var(--success-500);
  }
}
```

---

## 📱 Responsive Design {#responsive}

### Breakpoints
```css
/* Mobile First Approach */
/* xs: 0px - 479px (default, no media query) */
/* sm: 480px+ */
/* md: 768px+ */
/* lg: 1024px+ */
/* xl: 1280px+ */
/* 2xl: 1536px+ */

:root {
  --breakpoint-sm: 480px;
  --breakpoint-md: 768px;
  --breakpoint-lg: 1024px;
  --breakpoint-xl: 1280px;
  --breakpoint-2xl: 1536px;
}
```

### Responsive Patterns
```scss
/* Container */
.container {
  width: 100%;
  margin: 0 auto;
  padding: 0 var(--space-4);

  @media (min-width: 480px) {
    padding: 0 var(--space-6);
  }

  @media (min-width: 768px) {
    padding: 0 var(--space-8);
  }

  @media (min-width: 1024px) {
    max-width: 1200px;
  }
}

/* Responsive Grid */
.responsive-grid {
  display: grid;
  gap: var(--space-4);
  grid-template-columns: 1fr;

  @media (min-width: 768px) {
    grid-template-columns: repeat(2, 1fr);
    gap: var(--space-6);
  }

  @media (min-width: 1024px) {
    grid-template-columns: repeat(3, 1fr);
  }
}

/* Data Table Responsive */
.data-table-container {
  overflow-x: auto;
  
  .data-table {
    min-width: 600px;
    
    @media (min-width: 768px) {
      min-width: auto;
    }
  }
}

/* Button Stack on Mobile */
.button-group {
  display: flex;
  gap: var(--space-3);

  @media (max-width: 479px) {
    flex-direction: column;
    gap: var(--space-2);
  }
}
```

### Mobile-Specific Patterns
```scss
/* Touch-Friendly Sizing */
.btn {
  min-height: 44px; /* iOS guidelines */
  
  @media (max-width: 479px) {
    min-height: 48px; /* Larger touch targets on mobile */
  }
}

/* Mobile Form Layout */
.form-row {
  display: flex;
  gap: var(--space-4);

  @media (max-width: 767px) {
    flex-direction: column;
    gap: var(--space-3);
  }
}

/* Hidden on Mobile */
.mobile-hidden {
  @media (max-width: 767px) {
    display: none;
  }
}

/* Mobile Only */
.mobile-only {
  display: none;
  
  @media (max-width: 767px) {
    display: block;
  }
}
```

---

## ♿ Accesibilidad {#accesibilidad}

### Color Contrast
```scss
/* Ensure WCAG AA compliance (4.5:1 for normal text, 3:1 for large text) */
.text-primary {
  color: var(--gray-900); /* High contrast on white background */
}

.text-secondary {
  color: var(--gray-600); /* Meets 4.5:1 ratio */
}

.text-muted {
  color: var(--gray-500); /* Only for large text or non-essential content */
}
```

### Focus Management
```scss
/* Visible Focus Indicators */
.focus-visible {
  outline: 2px solid var(--brand-500);
  outline-offset: 2px;
  border-radius: var(--radius-sm);
}

/* Interactive Elements */
button, a, input, select, textarea {
  &:focus-visible {
    outline: 2px solid var(--brand-500);
    outline-offset: 2px;
  }
}

/* Skip Links */
.skip-link {
  position: absolute;
  top: -40px;
  left: 6px;
  background: var(--brand-500);
  color: white;
  padding: 8px;
  text-decoration: none;
  border-radius: var(--radius-sm);
  z-index: 1000;

  &:focus {
    top: 6px;
  }
}
```

### Semantic HTML
```html
<!-- ✅ Good: Semantic structure -->
<main>
  <header>
    <h1>Page Title</h1>
    <nav aria-label="Breadcrumb">
      <ol>
        <li><a href="/">Home</a></li>
        <li aria-current="page">Current Page</li>
      </ol>
    </nav>
  </header>

  <section>
    <h2>Section Title</h2>
    <!-- Content -->
  </section>
</main>

<!-- ✅ Forms with proper labels -->
<form>
  <div class="form-field">
    <label for="client-name">Client Name *</label>
    <input 
      id="client-name" 
      type="text" 
      required
      aria-describedby="name-error name-hint"
      aria-invalid="false">
    <div id="name-hint" class="field-hint">Enter the full name</div>
    <div id="name-error" class="field-error" aria-live="polite"></div>
  </div>
</form>

<!-- ✅ Data tables with headers -->
<table>
  <caption>List of clients</caption>
  <thead>
    <tr>
      <th scope="col">Name</th>
      <th scope="col">Email</th>
      <th scope="col">Actions</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>John Doe</td>
      <td>john@example.com</td>
      <td>
        <button aria-label="Edit John Doe">Edit</button>
      </td>
    </tr>
  </tbody>
</table>
```

### ARIA Attributes
```html
<!-- Loading States -->
<button aria-live="polite" aria-busy="true">
  <span class="visually-hidden">Loading...</span>
  <span aria-hidden="true">Save</span>
</button>

<!-- Modals -->
<div 
  role="dialog" 
  aria-labelledby="modal-title"
  aria-describedby="modal-description"
  aria-modal="true">
  <h2 id="modal-title">Confirm Delete</h2>
  <p id="modal-description">This action cannot be undone.</p>
</div>

<!-- Status Messages -->
<div role="status" aria-live="polite" class="success-message">
  Client created successfully
</div>

<!-- Form Validation -->
<input 
  aria-describedby="email-error"
  aria-invalid="true"
  aria-required="true">
<div id="email-error" role="alert">
  Please enter a valid email address
</div>
```

---

## ✨ Micro-interacciones {#microinteracciones}

### Hover Effects
```scss
/* Subtle Hover Transitions */
.interactive-element {
  transition: all 150ms ease;

  &:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  }
}

/* Button Hover */
.btn {
  transition: background-color 150ms ease, border-color 150ms ease, transform 150ms ease;

  &:hover:not(:disabled) {
    transform: translateY(-1px);
  }

  &:active:not(:disabled) {
    transform: translateY(0);
    transition-duration: 75ms;
  }
}

/* Card Hover */
.card {
  transition: box-shadow 200ms ease, transform 200ms ease;

  &.is-interactive:hover {
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    transform: translateY(-2px);
  }
}
```

### Loading Animations
```scss
/* Pulse Animation for Loading */
.pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

/* Slide In Animation */
.slide-in {
  animation: slideIn 300ms ease-out;
}

@keyframes slideIn {
  from {
    transform: translateY(10px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

/* Fade In Animation */
.fade-in {
  animation: fadeIn 200ms ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}
```

### Success Feedback
```scss
/* Success Checkmark Animation */
.success-check {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: var(--success-500);
  position: relative;

  &::after {
    content: '';
    position: absolute;
    top: 6px;
    left: 8px;
    width: 4px;
    height: 8px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
    animation: checkmark 300ms ease-in-out 200ms both;
  }
}

@keyframes checkmark {
  0% {
    opacity: 0;
    transform: rotate(45deg) scale(0.5);
  }
  100% {
    opacity: 1;
    transform: rotate(45deg) scale(1);
  }
}
```

---

## 📄 Guías por Tipo de Pantalla {#guias-pantalla}

### 1. Lista/Tabla (List Pages)
```scss
.list-page {
  .page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--space-6);

    .page-title {
      margin: 0;
    }

    .page-actions {
      display: flex;
      gap: var(--space-3);
    }
  }

  .filters-section {
    margin-bottom: var(--space-6);
    padding: var(--space-4);
    background: var(--gray-50);
    border-radius: var(--radius-lg);

    .filter-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: var(--space-4);
    }
  }

  .table-container {
    background: white;
    border-radius: var(--radius-lg);
    border: 1px solid var(--gray-200);
    overflow: hidden;

    .table-header {
      background: var(--gray-50);
      padding: var(--space-4);
      border-bottom: 1px solid var(--gray-200);

      .results-count {
        font-size: var(--text-sm);
        color: var(--gray-600);
      }
    }
  }
}
```

### 2. Formularios (Form Pages)
```scss
.form-page {
  max-width: 800px;
  margin: 0 auto;

  .form-header {
    margin-bottom: var(--space-8);
    text-align: center;

    .form-title {
      margin-bottom: var(--space-2);
    }

    .form-description {
      color: var(--gray-600);
      max-width: 500px;
      margin: 0 auto;
    }
  }

  .form-content {
    background: white;
    padding: var(--space-8);
    border-radius: var(--radius-lg);
    border: 1px solid var(--gray-200);

    .form-section {
      margin-bottom: var(--space-8);

      .section-title {
        margin-bottom: var(--space-4);
        padding-bottom: var(--space-2);
        border-bottom: 1px solid var(--gray-200);
      }

      .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: var(--space-4);
      }
    }

    .form-actions {
      display: flex;
      justify-content: space-between;
      padding-top: var(--space-6);
      border-top: 1px solid var(--gray-200);

      .secondary-actions {
        display: flex;
        gap: var(--space-3);
      }

      .primary-actions {
        display: flex;
        gap: var(--space-3);
      }
    }
  }
}
```

### 3. Dashboard/Resumen
```scss
.dashboard-page {
  .dashboard-header {
    margin-bottom: var(--space-8);

    .welcome-section {
      margin-bottom: var(--space-6);

      .welcome-title {
        font-size: var(--text-2xl);
        margin-bottom: var(--space-2);
      }

      .welcome-subtitle {
        color: var(--gray-600);
      }
    }

    .quick-stats {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: var(--space-4);

      .stat-card {
        background: white;
        padding: var(--space-6);
        border-radius: var(--radius-lg);
        border: 1px solid var(--gray-200);

        .stat-value {
          font-size: var(--text-3xl);
          font-weight: var(--font-bold);
          color: var(--brand-500);
          margin-bottom: var(--space-1);
        }

        .stat-label {
          font-size: var(--text-sm);
          color: var(--gray-600);
        }

        .stat-change {
          font-size: var(--text-xs);
          margin-top: var(--space-2);

          &.positive { color: var(--success-600); }
          &.negative { color: var(--error-600); }
        }
      }
    }
  }

  .dashboard-content {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: var(--space-8);

    .main-content {
      .content-section {
        margin-bottom: var(--space-8);

        .section-header {
          display: flex;
          justify-content: space-between;
          align-items: center;
          margin-bottom: var(--space-4);
        }
      }
    }

    .sidebar {
      .sidebar-section {
        background: white;
        padding: var(--space-6);
        border-radius: var(--radius-lg);
        border: 1px solid var(--gray-200);
        margin-bottom: var(--space-6);
      }
    }
  }
}
```

### 4. Detalle/Vista
```scss
.detail-page {
  .detail-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: var(--space-8);
    padding-bottom: var(--space-6);
    border-bottom: 1px solid var(--gray-200);

    .detail-info {
      .detail-title {
        margin-bottom: var(--space-2);
      }

      .detail-meta {
        display: flex;
        gap: var(--space-4);
        font-size: var(--text-sm);
        color: var(--gray-500);

        .meta-item {
          display: flex;
          align-items: center;
          gap: var(--space-1);
        }
      }
    }

    .detail-actions {
      display: flex;
      gap: var(--space-3);
    }
  }

  .detail-content {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: var(--space-8);

    .main-details {
      .detail-section {
        margin-bottom: var(--space-8);

        .section-title {
          margin-bottom: var(--space-4);
        }

        .detail-grid {
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
          gap: var(--space-4);

          .detail-item {
            .item-label {
              font-size: var(--text-sm);
              font-weight: var(--font-medium);
              color: var(--gray-600);
              margin-bottom: var(--space-1);
            }

            .item-value {
              font-size: var(--text-base);
              color: var(--gray-900);
            }
          }
        }
      }
    }

    .detail-sidebar {
      .sidebar-section {
        background: var(--gray-50);
        padding: var(--space-6);
        border-radius: var(--radius-lg);
        margin-bottom: var(--space-6);
      }
    }
  }
}
```

---

## 🎯 Checklist de Calidad UI/UX

### ✅ Visual Design
- [ ] **Colores**: Solo design tokens, no valores hardcoded
- [ ] **Tipografía**: Jerarquía clara y legible
- [ ] **Spacing**: Consistente usando sistema de spacing
- [ ] **Borders y Radius**: Aplicados consistentemente
- [ ] **Shadows**: Usadas apropiadamente para depth

### ✅ Interactivity
- [ ] **Hover States**: Todos los elementos interactivos tienen hover
- [ ] **Focus States**: Visibles en todos los inputs y botones
- [ ] **Loading States**: Durante operaciones asíncronas
- [ ] **Disabled States**: Para elementos no disponibles
- [ ] **Error States**: Claros y accionables

### ✅ Responsive
- [ ] **Mobile First**: Diseñado mobile-first
- [ ] **Breakpoints**: Funciona en todos los tamaños
- [ ] **Touch Targets**: Mínimo 44px en mobile
- [ ] **Horizontal Scroll**: Evitado en containers
- [ ] **Text Size**: Legible en dispositivos pequeños

### ✅ Accessibility
- [ ] **Contrast**: WCAG AA compliance (4.5:1)
- [ ] **Keyboard Navigation**: Todos los elementos accesibles
- [ ] **Screen Readers**: ARIA labels apropiados
- [ ] **Focus Management**: Lógico y visible
- [ ] **Alt Text**: Para todas las imágenes

### ✅ Performance
- [ ] **Animations**: 60fps y respetan prefers-reduced-motion
- [ ] **Images**: Optimizadas y lazy loaded
- [ ] **Bundle Size**: Componentes no impactan bundle size
- [ ] **Rendering**: No layout thrashing
- [ ] **Memory**: No memory leaks en animations

### ✅ Content
- [ ] **i18n Ready**: Todos los textos traducibles
- [ ] **Error Messages**: Claros y accionables
- [ ] **Empty States**: Útiles y orientan al usuario
- [ ] **Loading Messages**: Informan progreso
- [ ] **Success Feedback**: Confirman acciones completadas

---

## 📚 Recursos y Referencias

### Design Tools
- **Figma Components**: [Design System Library](figma://design-system)
- **Icons**: [Lucide Icons](https://lucide.dev/)
- **Illustrations**: [unDraw](https://undraw.co/)

### Testing Tools
- **Accessibility**: [axe DevTools](https://www.deque.com/axe/devtools/)
- **Color Contrast**: [WebAIM Contrast Checker](https://webaim.org/resources/contrastchecker/)
- **Responsive Testing**: [Responsive Design Checker](https://responsivedesignchecker.com/)

### Guidelines Reference
- **WCAG 2.1**: [Web Content Accessibility Guidelines](https://www.w3.org/WAI/WCAG21/quickref/)
- **Material Design**: [Material Design Guidelines](https://material.io/design)
- **Human Interface Guidelines**: [Apple HIG](https://developer.apple.com/design/human-interface-guidelines/)

---

**Mantén estos estándares en cada pantalla para garantizar una experiencia de usuario consistente, profesional y accesible en todo el sistema Boukii V5.**