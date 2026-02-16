## OVERVIEW PROJECT

Aplikasi supply management untuk usaha B2B supply bahan baku makanan (sayuran, buah, daging, bumbu-bumbu, dll).

**Tech Stack:** Laravel 11.x (Fullstack) + MySQL/MariaDB + Bootstrap 5

**Karakteristik Bisnis:**
- Model B2B, setiap client memiliki harga berbeda per item
- PO dibuat oleh internal tim (client tidak memiliki akses)
- Tracking lengkap harga beli & jual untuk analisis profit
- Support partial payment dan multi payment terms
- Aplikasi simple namun comprehensive untuk operasional harian

---

## TECH STACK REQUIREMENTS

### Core Framework
- Laravel 11.x dengan Blade Template Engine
- MySQL 8.x atau MariaDB 10.x
- Bootstrap 5.3 untuk UI
- JQuery
- Chart.js untuk visualisasi dashboard

### Libraries & Packages
- Laravel Breeze untuk authentication system
- DomPDF Laravel untuk PDF generation
- Laravel Excel (PHPSpreadsheet) untuk export Excel
- Laravel Mail dengan Queue untuk email notifications
- Intervention Image untuk upload dan resize gambar

---

## USER ROLES & ACCESS CONTROL

### Superadmin
- Hanya departemen keuangan
- Approve/reject semua PO
- Akses penuh ke semua fitur dan laporan
- Manage master data (users, clients, items, categories)
- Lihat semua laporan keuangan detail

### Staff
- Buat dan edit PO (sebelum approved)
- Input harga beli dan harga jual
- Generate Surat Jalan dan Invoice
- Input pengeluaran lain-lain
- Lihat laporan basic (tidak detail keuangan)

---

## DATABASE SCHEMA STRUCTURE

### Master Data Tables

**users**
- Basic auth fields (name, email, password, role)
- Soft deletes enabled

**categories**
- Untuk kategorisasi item (Sayuran, Buah, Daging, Bumbu, dll)
- Field: name, description, is_active

**items**
- Field: category_id, code, name, description, unit, notes, photo
- Unit: kg, liter, pcs, karton, dll (text field, tidak fixed)
- Photo optional untuk referensi visual
- is_active untuk item yang discontinued
- Soft deletes enabled

**clients**
- Field: code, name, address, email, phone, pic_name, pic_phone, npwp
- Optional: latitude, longitude, logo
- payment_terms (integer, dalam hari: 0=COD, 7, 14, 30, custom)
- credit_limit (decimal untuk batas kredit)
- is_active untuk client tidak aktif
- Soft deletes enabled

### Transaction Tables

**purchase_orders**
- po_number format: PO/YYYY/MM/001
- Relations: client_id, created_by (staff_id), approved_by
- Dates: po_date, delivery_date, approved_at
- status: draft, pending_approval, approved, rejected, cancelled, completed
- notes, rejected_reason, cancelled_reason
- Soft deletes

**purchase_order_items**
- Relations: purchase_order_id, item_id
- quantity, unit (bisa beda dengan master item)
- purchase_price (harga beli, diisi setelah approved & pembelian dilakukan)
- selling_price (harga jual, bisa diubah, auto-fill dari history)
- notes (optional, untuk catatan khusus item)

**item_price_history**
- Track semua perubahan harga per client per item
- client_id, item_id, purchase_price, selling_price
- changed_by, changed_at, reference_type (po/invoice/manual), reference_id

**modals**
- Modal pembelian untuk tracking kas keluar
- modal_number format: MOD/YYYY/MM/001
- total_amount, modal_date, notes, created_by

**modal_allocations**
- Alokasi modal ke PO (bisa 1 modal untuk beberapa PO)
- modal_id, purchase_order_id, allocated_amount

**delivery_notes**
- Surat jalan dengan kode unik
- dn_number format: SJ/YYYY/MM/001
- purchase_order_id, client_id, dn_date
- delivery_type: full (semua item PO) atau partial (sebagian)
- status: draft, sent, received
- created_by, notes

**delivery_note_items**
- Items yang dikirim dalam surat jalan
- delivery_note_id, po_item_id, item_id
- quantity_delivered, unit

**invoices**
- invoice_number format: INV/YYYY/MM/001
- purchase_order_id, client_id, invoice_date, due_date
- subtotal, discount_type (percentage/fixed), discount_value
- tax_percentage, tax_amount
- total_amount, paid_amount, remaining_amount
- status: unpaid, partial, paid, overdue
- notes, created_by

**invoice_items**
- Items dalam invoice dengan harga final
- invoice_id, po_item_id, item_id
- quantity, unit, unit_price, subtotal

**payments**
- Track pembayaran invoice (support partial payment)
- invoice_id, payment_date, amount, payment_method (transfer/cash/giro)
- reference_number (no transfer/giro), notes
- created_by

**expenses**
- Pengeluaran lain-lain (bensin, makan, rokok, dll)
- expense_number format: EXP/YYYY/MM/001
- category (bensin, makan, operasional, dll)
- amount, expense_date, description
- receipt_file (optional upload bukti)
- created_by

---

## BUSINESS LOGIC & WORKFLOW

### PO Status Flow

**Draft**
- Staff/Superadmin membuat PO baru
- Pilih client, items dengan quantity dan unit
- Harga jual auto-fill dari history terakhir (client + item sama)
- Bisa edit harga jual jika berbeda
- Bisa save as draft atau langsung submit approval

**Pending Approval**
- Setelah submit, status jadi pending_approval
- Staff masih bisa edit PO yang pending
- Notifikasi email ke Superadmin

**Approved**
- Superadmin approve PO
- Setelah approved, Staff input modal pembelian:
  - Bisa pilih beberapa PO sekaligus
  - Alokasi manual per PO atau rata otomatis
- Kemudian Staff input harga beli per item setelah pembelian fisik
- Staff bisa update harga jual jika ada perubahan
- Semua perubahan harga masuk history

**Rejected**
- Superadmin bisa reject dengan alasan
- PO kembali ke Draft, Staff bisa edit dan submit ulang

**Cancelled**
- PO yang sudah approved bisa dibatalkan (dengan alasan)
- Tidak bisa generate Surat Jalan/Invoice setelah cancelled

**Completed**
- Setelah Invoice generated dan paid

### Surat Jalan Generation

**Input Data:**
- Pilih PO yang sudah approved
- Pilih delivery type: Full atau Partial
- Jika Partial: pilih items mana saja yang dikirim dengan quantity
- Generate kode unik SJ/YYYY/MM/001
- Bisa generate PDF untuk print

**Business Rules:**
- 1 PO bisa punya beberapa Surat Jalan (partial delivery)
- Surat Jalan bisa di-regenerate PDFnya jika ada kesalahan
- Status: draft (belum dikirim), sent (sudah dikirim), received (diterima client)

### Invoice Generation

**Input Data:**
- Pilih PO atau Surat Jalan yang belum di-invoice
- Sistem auto calculate subtotal dari items
- Input: discount (percentage atau fixed amount), tax percentage
- Due date auto calculate berdasarkan payment_terms client
- Preview sebelum generate

**Before Generate:**
- Bisa edit harga jual per item terakhir kali
- Set discount dan tax
- Confirm data

**After Generate:**
- Invoice number: INV/YYYY/MM/001
- Status: unpaid
- Generate PDF untuk kirim ke client
- Email otomatis ke client (jika ada email)

**Business Rules:**
- 1 PO = 1 Invoice (tidak gabung beberapa PO)
- Invoice bisa di-regenerate PDF jika ada kesalahan
- Harga di invoice adalah harga final (masuk history jika berbeda)

### Payment Tracking

**Recording Payment:**
- Pilih invoice yang unpaid/partial
- Input: payment_date, amount, payment_method, reference_number
- Sistem auto update paid_amount dan remaining_amount
- Update status:
  - partial: jika paid_amount < total_amount
  - paid: jika paid_amount >= total_amount
  - overdue: jika due_date terlewat dan belum paid

**Business Rules:**
- Support multiple payments untuk 1 invoice (partial payment)
- Total payments tidak boleh melebihi total invoice
- Payment history tersimpan semua

### Batch Invoice

**Fungsi:**
- Generate multiple invoices sekaligus
- Filter: pilih 1 client, pilih PO/Surat Jalan yang belum di-invoice
- Set tanggal invoice yang sama untuk semua
- Batch generate PDF semua invoice

**Business Rules:**
- Hanya untuk 1 client dalam 1 batch
- Bisa pilih PO mana saja dari client tersebut
- Due date tetap per invoice sesuai payment_terms

### Modal & Pengeluaran

**Modal Management:**
- Input total modal untuk pembelian
- Alokasi ke 1 atau beberapa PO:
  - Pilih PO yang sudah approved
  - Alokasi manual per PO atau split otomatis
- Track total modal vs total alokasi

**Pengeluaran Lain-lain:**
- Input langsung tanpa approval
- Kategori: bensin, makan, rokok, operasional, dll
- Upload bukti optional
- Langsung masuk laporan

---

## FITUR UTAMA APLIKASI

### Master Data Management

**Category Management**
- CRUD categories
- Set active/inactive

**Item Management**
- CRUD items dengan kategori
- Upload photo optional
- Set active/inactive (discontinued items tetap ada di history)
- Lihat price history per client

**Client Management**
- CRUD clients
- Input payment terms (hari) dan credit limit
- Optional: coordinates (lat/long) dan logo
- Set active/inactive
- Check credit limit vs outstanding invoices

**User Management** (Superadmin only)
- CRUD users
- Set role (superadmin/staff)
- Reset password

### Purchase Order Management

**Create PO**
- Select client (auto-load payment terms)
- Add items:
  - Search/select item
  - Input quantity dan unit
  - Harga jual auto-fill dari history (jika ada)
  - Override harga jika perlu
  - Notes per item
- Save as draft atau submit untuk approval

**PO List & Filter**
- Filter by: status, client, date range, staff
- Search by PO number
- Bulk actions untuk Superadmin

**PO Detail View**
- Show all info: client, items, prices, status
- Timeline approval
- Action buttons sesuai status dan role

**Approval System** (Superadmin only)
- List pending POs dengan notifikasi badge
- Approve atau reject dengan notes
- Email notification ke Staff

**Modal Input** (setelah approved)
- Form input modal dengan alokasi:
  - Total modal amount
  - Pilih PO yang sudah approved
  - Alokasi per PO (manual atau auto-split)
  - Validasi: total alokasi tidak boleh > total modal

**Purchase Price Input** (setelah pembelian)
- Form input harga beli per item di PO
- Bisa update harga jual juga
- Auto save ke price history

### Surat Jalan Management

**Generate Surat Jalan**
- Pilih PO approved
- Choose delivery type:
  - Full: semua items di PO
  - Partial: pilih items dan quantity yang dikirim
- Generate kode unik: SJ/YYYY/MM/001
- Preview sebelum save

**Surat Jalan List**
- Filter by: client, date, PO, status
- Search by SJ number
- View dan regenerate PDF

**Surat Jalan Detail**
- Show info lengkap
- Items yang dikirim
- Link ke PO dan Invoice (jika ada)
- Download PDF button

**PDF Format:**
- Header: logo perusahaan, SJ number, date
- Client info: name, address, PIC
- Table items: nama item, quantity, unit, notes
- Footer: tanda tangan pengirim dan penerima (space kosong untuk TTD basah)

### Invoice Management

**Generate Invoice**
- Pilih PO/Surat Jalan yang belum di-invoice
- Review items dan harga:
  - Bisa edit harga jual terakhir kali sebelum generate
  - Set discount (percentage atau fixed Rp)
  - Set tax percentage (bisa 0%)
- Auto calculate:
  - Subtotal
  - Discount amount
  - Tax amount
  - Total
- Set due date (auto dari payment_terms atau manual)
- Preview calculation

**Invoice List**
- Filter by: status, client, date range, overdue
- Search by invoice number
- Badge untuk overdue invoices
- Quick view payment status

**Invoice Detail**
- Complete info
- Items dengan harga final
- Calculation breakdown
- Payment history (jika ada)
- Download PDF
- Record payment button

**PDF Format:**
- Header: logo, invoice number, dates (invoice & due)
- Client info lengkap
- Table items: nama, qty, unit, harga, subtotal
- Calculation: subtotal, discount, tax, total
- Payment info: paid amount, remaining
- Notes dan payment instructions

**Batch Invoice Generation**
- Select client
- Filter unpaid POs/Surat Jalan dari client tersebut
- Set invoice date (sama untuk semua)
- Preview list yang akan di-generate
- Batch generate dengan sequential numbering
- Batch download PDF (zip file)

### Payment Management

**Record Payment**
- Select invoice (unpaid atau partial)
- Input:
  - Payment date
  - Amount (validasi: tidak boleh > remaining)
  - Payment method (dropdown: Transfer, Cash, Giro, etc)
  - Reference number (no transfer/giro)
  - Notes optional
- Auto update invoice status

**Payment History**
- List all payments dengan filter
- Search by invoice number atau reference number
- Export to Excel

**Overdue Tracking**
- Dashboard widget untuk overdue invoices
- Auto email reminder H-3 dan H-overdue
- List overdue dengan aging (berapa hari telat)

### Expense Management

**Input Expense**
- Form simple:
  - Category (dropdown atau free text)
  - Amount
  - Date
  - Description
  - Upload receipt (optional, gambar/PDF)
- Langsung save, tidak perlu approval

**Expense List**
- Filter by category, date range
- Search by description
- View receipt
- Export to Excel

**Expense Categories:** (bisa custom)
- Bensin
- Makan
- Rokok
- Transportasi
- Operasional
- Lain-lain

### Laporan & Dashboard

**Dashboard Superadmin**

Widgets:
- Total penjualan bulan ini (Rp)
- Total invoice unpaid (Rp dan count)
- PO pending approval (count dengan badge)
- Top 5 clients by sales volume
- Grafik penjualan 6 bulan terakhir (bar/line chart)
- Grafik laba/rugi bulanan (line chart)
- Recent activities (latest POs, Invoices, Payments)

**Dashboard Staff**

Widgets (limited):
- PO draft count
- PO pending approval count
- Invoice unpaid count (yang mereka buat)
- Recent activities (own data)

**Laporan Transaksi Bulanan**

Filter: bulan, tahun, client (optional)

Show:
- Total penjualan (revenue)
- Total pembelian (COGS)
- Gross profit
- Total pengeluaran lain-lain
- Net profit/loss
- Profit margin percentage

Detail breakdown:
- Per client: penjualan, pembelian, profit
- Per category item: volume, revenue
- Top 10 items by revenue
- Top 10 items by profit margin

**Laporan Laba/Rugi**

Calculation:
```
Revenue = SUM(invoices.total_amount WHERE status=paid)
COGS = SUM(po_items.purchase_price * quantity)
Gross Profit = Revenue - COGS
Expenses = SUM(expenses.amount) + SUM(discount) + SUM(tax paid if applicable)
Net Profit = Gross Profit - Expenses
```

View options:
- Monthly summary
- Per client detail
- Per item category
- Overall summary

Export:
- PDF formatted report
- Excel with raw data

**Laporan Detail Per Client**

Filter: client, date range

Show:
- Total transaksi (count PO, invoices)
- Total penjualan
- Total pembayaran (paid)
- Total outstanding (unpaid + partial)
- Average payment days
- Overdue amount
- Credit limit vs outstanding

List transaksi:
- Table PO/Invoice dengan status
- Detail items
- Payment history

Export: PDF dan Excel

**Laporan Item Performance**

Filter: date range, category

Show per item:
- Total quantity sold
- Total revenue
- Average selling price
- Purchase price range
- Average profit per unit
- Total profit
- Profit margin percentage

Sort by: revenue, profit, quantity

Export: Excel

---

## UI/UX REQUIREMENTS

### Layout Structure

**Sidebar Navigation** (collapse on mobile)

Superadmin Menu:
- Dashboard
- Master Data
  - Categories
  - Items
  - Clients
  - Users
- Transactions
  - Purchase Orders
  - Surat Jalan
  - Invoices
  - Payments
- Finance
  - Modal Management
  - Expenses
  - Reports
- Settings

Staff Menu (limited):
- Dashboard
- Transactions
  - Purchase Orders
  - Surat Jalan
  - Invoices
  - Payments
- Finance
  - Expenses
  - Basic Reports

**Top Navbar:**
- Logo/brand name
- Notification bell (pending approvals, overdue invoices)
- User dropdown (profile, logout)

### Design Guidelines

**Bootstrap 5 Components:**
- Use Cards untuk grouping content
- Tables dengan DataTables.js (sorting, search, pagination)
- Modals untuk forms dan confirmations
- Badges untuk status indicators
- Alerts untuk success/error messages
- Breadcrumbs untuk navigation
- Toasts untuk non-intrusive notifications

**Color Scheme:**
- Primary: Bootstrap primary (bisa custom)
- Success: green untuk paid, approved
- Warning: yellow untuk pending, partial
- Danger: red untuk rejected, overdue, cancelled
- Info: blue untuk draft, info messages

**Form Design:**
- Clear labels
- Validation messages (Laravel validation)
- Required fields marked dengan asterisk
- Help text untuk field yang perlu penjelasan
- Disabled fields jika read-only

**Table Design:**
- Responsive (horizontal scroll on mobile)
- Action buttons di column terakhir
- Status badges dengan color coding
- Sortable columns
- Search dan filter di atas table
- Pagination dengan page size options

**Responsive Design:**
- Mobile-first approach
- Collapse sidebar on mobile (hamburger menu)
- Stack form fields on mobile
- Horizontal scroll untuk wide tables
- Touch-friendly button sizes

### User Experience

**Loading States:**
- Show spinner untuk async operations
- Disable buttons saat submit untuk prevent double-click
- Progress indicator untuk batch operations

**Confirmations:**
- Modal confirm untuk delete actions
- Sweet alert/modal untuk critical actions (approve, reject, cancel)

**Success/Error Feedback:**
- Toast notifications untuk success
- Alert messages untuk errors
- Field-level validation errors

**Autocomplete & Select:**
- Select2 atau Choices.js untuk searchable dropdowns
- Client selector dengan autocomplete
- Item selector dengan search dan category filter

**Date Pickers:**
- Flatpickr atau Bootstrap Datepicker
- Format: DD/MM/YYYY atau YYYY-MM-DD (configurable)
- Range picker untuk date filters

---

## PDF GENERATION REQUIREMENTS

### Surat Jalan PDF

**Layout:**
- A4 portrait
- Header section:
  - Logo perusahaan (kiri)
  - Company info (nama, alamat, telepon) (kanan)
  - Judul: "SURAT JALAN" (center, bold)
  - SJ Number dan Date
- Client section:
  - Kepada: Client name
  - Alamat: Client address
  - PIC: PIC name dan phone
- Items table:
  - Columns: No, Nama Item, Jumlah, Satuan, Keterangan
  - Borders, zebra striping
- Footer section:
  - Catatan (jika ada)
  - Signature boxes:
    - Pengirim (kiri): Nama, TTD (space), Tanggal
    - Penerima (kanan): Nama, TTD (space), Tanggal

**Features:**
- Watermark "COPY" jika re-generate
- QR Code (optional, untuk tracking)

### Invoice PDF

**Layout:**
- A4 portrait
- Header:
  - Logo dan company info
  - Judul: "INVOICE"
  - Invoice number
  - Dates: Invoice date, Due date
- Client section (sama seperti SJ)
- Items table:
  - Columns: No, Item, Qty, Unit, Harga Satuan, Subtotal
  - Format currency IDR
- Calculation section:
  - Subtotal
  - Discount (jika ada)
  - Subtotal after discount
  - Tax (PPN, jika ada)
  - TOTAL (bold, highlighted)
- Payment status:
  - Paid amount (jika ada)
  - Remaining amount
- Payment info section:
  - Bank details
  - Payment instructions
- Footer:
  - Terms & conditions
  - Thank you note

**Features:**
- Color coding: Red untuk overdue, Green untuk paid
- Stamp "LUNAS" jika fully paid (optional)

### Laporan PDF

**Format:**
- Professional business report layout
- Cover page dengan logo
- Table of contents (untuk laporan panjang)
- Page numbers
- Charts/graphs embedded (jika ada)
- Landscape untuk wide tables

---

## EMAIL NOTIFICATION REQUIREMENTS

### Email Templates (Bootstrap Email)

**PO Approval Request** (ke Superadmin)
- Subject: "PO {po_number} Menunggu Approval"
- Body:
  - Info: Staff name, PO number, client, date
  - Items summary (count)
  - Total amount
  - Link button ke PO detail
  - Notes dari Staff

**PO Approved** (ke Staff)
- Subject: "PO {po_number} Telah Disetujui"
- Body:
  - Info: PO number, client, approved by, approved at
  - Next action: Input modal dan harga beli
  - Link ke PO detail

**PO Rejected** (ke Staff)
- Subject: "PO {po_number} Ditolak"
- Body:
  - Info: PO number, rejected by, rejected at
  - Reason for rejection
  - Action: Edit dan submit ulang
  - Link ke PO edit

**Invoice Created** (ke Client - optional jika ada email)
- Subject: "Invoice {invoice_number} dari {company_name}"
- Body:
  - Invoice number, date, due date
  - Total amount
  - Payment instructions
  - Attach PDF invoice
  - Thank you note

**Payment Reminder** (ke Client, H-3 before due date)
- Subject: "Reminder: Invoice {invoice_number} Jatuh Tempo {due_date}"
- Body:
  - Friendly reminder
  - Invoice details
  - Remaining amount
  - Payment instructions

**Overdue Notice** (ke Client dan Superadmin)
- Subject: "OVERDUE: Invoice {invoice_number}"
- Body:
  - Invoice overdue info
  - Days overdue
  - Total outstanding
  - Urgent payment request

### Email Queue

- Gunakan Laravel Queue untuk async sending
- Retry logic: 3x attempts
- Log semua sent emails
- Handle failures gracefully

---

## VALIDATION & BUSINESS RULES

### Purchase Order

**Create/Edit:**
- Client wajib dipilih
- Minimal 1 item
- Quantity harus > 0
- Selling price harus > 0
- Tidak bisa submit approval jika ada field kosong

**Approval:**
- Hanya Superadmin yang bisa approve
- PO status harus pending_approval
- Reject harus ada reason

**Cancel:**
- Hanya bisa cancel jika status = approved
- Tidak bisa cancel jika sudah ada Surat Jalan
- Cancel harus ada reason

### Modal Allocation

- Total alokasi tidak boleh > total modal
- Hanya bisa alokasi ke PO yang statusnya approved
- 1 PO tidak boleh dapat alokasi dari 2 modal berbeda (atau boleh? - klarifikasi jika perlu)

### Surat Jalan

- Hanya bisa generate dari PO yang approved
- Jika partial: quantity delivered tidak boleh > quantity di PO
- Tidak bisa partial jika sudah full delivery sebelumnya
- Kode SJ harus unique

### Invoice

- Hanya bisa generate dari PO/SJ yang belum di-invoice
- Discount tidak boleh > subtotal
- Tax percentage 0-100%
- Due date harus >= invoice date
- Kode invoice harus unique

### Payment

- Amount tidak boleh > remaining amount invoice
- Payment date tidak boleh > today
- Tidak bisa payment jika invoice sudah paid (remaining = 0)

### Credit Limit

- Check credit limit sebelum approve PO:
  - Total outstanding invoices + PO amount <= credit limit
- Warning jika mendekati limit (90%)
- Block jika exceed limit

### Price History

- Auto save history setiap perubahan harga
- Track siapa yang mengubah (user_id)
- Track reference (dari PO/Invoice mana)

---

## SECURITY REQUIREMENTS

### Authentication & Authorization

- Laravel Breeze default auth
- Password minimum 8 characters
- Session timeout: 2 hours idle
- Remember me option
- Middleware untuk setiap route:
  - auth: untuk semua authenticated users
  - role:superadmin untuk Superadmin-only routes

### Data Protection

- Soft deletes untuk semua master data
- Tidak boleh hard delete jika ada relasi ke transaksi
- Validasi CSRF untuk semua forms
- XSS protection (Laravel default)
- SQL injection protection (Eloquent ORM)

### File Upload

- Validate file type (images: jpg, png; docs: pdf)
- Max file size: 2MB untuk images, 5MB untuk PDFs
- Store di storage/app/public (symlink ke public/storage)
- Sanitize filename
- Generate unique filename untuk prevent overwrite

### API/Endpoints (jika ada)

- Rate limiting untuk prevent abuse
- API token authentication
- CORS configuration jika perlu

---

## PERFORMANCE OPTIMIZATION

### Database

- Index pada foreign keys
- Index pada fields yang sering di-search/filter:
  - po_number, invoice_number, dn_number
  - client_id, item_id, user_id
  - status, dates
- Eager loading untuk prevent N+1 queries
- Pagination untuk list pages (50 items per page default)

### Caching

- Cache master data (categories, items) yang jarang berubah
- Cache user permissions
- Cache config settings
- Clear cache on update

### File Storage

- Optimize images on upload (resize, compress)
- Store PDFs dengan unique filename
- Periodic cleanup untuk unused files

### Frontend

- Minimize HTTP requests
- Compress CSS/JS (Laravel Mix atau Vite)
- Lazy load images
- Use CDN untuk libraries (Bootstrap, jQuery, etc)

---

## DEVELOPMENT WORKFLOW & PROGRESS TRACKING

### Project Structure

Folder organization:
```
app/
├── Http/
│   ├── Controllers/
│   ├── Middleware/
│   └── Requests/
├── Models/
├── Services/
└── Helpers/

database/
├── migrations/
├── seeders/
└── factories/

resources/
├── views/
│   ├── layouts/
│   ├── components/
│   ├── master/
│   ├── transaction/
│   └── report/
├── css/
└── js/

public/
├── css/
├── js/
├── images/
└── storage/ (symlink)

storage/
├── app/
│   ├── public/
│   │   ├── items/
│   │   ├── clients/
│   │   ├── receipts/
│   │   └── pdfs/
└── logs/

routes/
└── web.php

config/
tests/
```

### Development Batches

Bagi development menjadi beberapa batch sesuai prioritas:

**BATCH 1: Foundation & Authentication**
- Setup Laravel project fresh
- Install dependencies (Bootstrap, libraries)
- Configure database
- Setup authentication (Laravel Breeze)
- Create base layout (sidebar, navbar, footer)
- User management CRUD
- Role middleware

**BATCH 2: Master Data Management**
- Categories CRUD
- Items CRUD (dengan upload photo)
- Clients CRUD (dengan upload logo, coordinates)
- Master data list dengan search & filter
- Soft delete implementation
- Seeders untuk dummy data

**BATCH 3: Purchase Order Management**
- PO CRUD (draft, submit)
- PO approval system (approve/reject)
- Email notifications
- PO list dengan filters
- Status tracking
- Price auto-fill from history

**BATCH 4: Modal & Purchase Price**
- Modal management CRUD
- Modal allocation form
- Purchase price input form
- Price history tracking
- Modal report

**BATCH 5: Surat Jalan**
- Generate Surat Jalan (full/partial)
- SJ list & detail
- PDF generation & download
- SJ status tracking

**BATCH 6: Invoice Management**
- Generate invoice from PO/SJ
- Invoice detail dengan calculation
- Price editing before generate
- Discount & tax calculation
- PDF generation
- Batch invoice generation

**BATCH 7: Payment Tracking**
- Payment recording (partial support)
- Payment history
- Invoice status update
- Overdue tracking
- Payment list & filters

**BATCH 8: Expense Management**
- Expense input form
- Receipt upload
- Expense list & filters
- Expense categories

**BATCH 9: Reports & Dashboard**
- Dashboard widgets (Superadmin & Staff)
- Laporan transaksi bulanan
- Laporan laba/rugi
- Laporan per client
- Laporan item performance
- Export PDF & Excel
- Charts implementation (Chart.js)

**BATCH 10: Polish & Testing**
- Credit limit checking
- Email templates finalization
- UI/UX improvements
- Responsive testing
- Bug fixes
- Performance optimization
- Documentation

### Progress Tracking File

Buat file **PROGRESS.md** di root project dengan format:

```
# PROGRESS TRACKING - SUPPLY MANAGEMENT APP

## Project Info
- Start Date: [tanggal]
- Target Completion: [estimasi]
- Developer: [nama]

---

## BATCH 1: Foundation & Authentication
Status: [Not Started / In Progress / Testing / Completed]
Start: [tanggal]
End: [tanggal]

### Tasks:
- [ ] Laravel fresh install
- [ ] Configure .env (database, mail)
- [ ] Install Laravel Breeze
- [ ] Install Bootstrap 5
- [ ] Create base layout
- [ ] User CRUD
- [ ] Role middleware
- [ ] Test authentication flow

### Notes:
- [catat issue, decision, atau catatan penting]

---

## BATCH 2: Master Data Management
Status: [status]
Start: [tanggal]
End: [tanggal]

### Tasks:
- [ ] Categories migration & model
- [ ] Categories CRUD
- [ ] Items migration & model
- [ ] Items CRUD with photo upload
- [ ] Clients migration & model
- [ ] Clients CRUD with logo upload
- [ ] Implement soft deletes
- [ ] Create seeders
- [ ] Test all CRUD

### Notes:
- [notes]

---

[lanjutkan untuk batch selanjutnya...]

---

## Issues & Bugs
### Open:
- [list open issues]

### Resolved:
- [list resolved issues dengan tanggal]

---

## Future Enhancements
- [list fitur tambahan untuk future development]
```

Update file ini setiap kali progress:
- Checklist task yang selesai
- Catat tanggal start & end tiap batch
- Tulis notes penting (keputusan teknis, issue, solusi)
- Track bugs dan resolutions

---

## TESTING REQUIREMENTS

### Manual Testing Checklist

Setiap batch harus ditest:

**Functionality Testing:**
- Semua CRUD operations
- Form validation (client-side & server-side)
- File uploads
- PDF generation
- Email sending
- Calculations (invoice, profit/loss)

**Security Testing:**
- Authorization (role-based access)
- CSRF protection
- XSS prevention
- SQL injection attempts
- File upload validation

**UI/UX Testing:**
- Responsive design (desktop, tablet, mobile)
- Browser compatibility (Chrome, Firefox, Safari, Edge)
- Loading states
- Error messages
- Success notifications

**Integration Testing:**
- Workflow end-to-end:
  - PO creation → Approval → Modal → Prices → SJ → Invoice → Payment
- Email notifications
- PDF generation
- Report generation

### Test Data

Create seeders untuk:
- 2 Users (1 Superadmin, 1 Staff)
- 5 Categories
- 20 Items (berbagai kategori)
- 10 Clients (berbagai payment terms)
- Dummy transactions untuk testing reports

---

## DEPLOYMENT PREPARATION

### Server Requirements

- PHP 8.2+
- MySQL 8.0+ atau MariaDB 10.6+
- Composer
- Node.js & NPM (untuk build assets)
- Wkhtmltopdf (jika pakai Snappy untuk PDF)

### Environment Setup

**.env production:**
- APP_ENV=production
- APP_DEBUG=false
- Database credentials
- Mail configuration (SMTP)
- Queue driver (database atau redis)
- File permissions: storage & bootstrap/cache writable

### Pre-Deployment Checklist

- [ ] Run migrations
- [ ] Run seeders (categories, dummy data if needed)
- [ ] Build assets (npm run build)
- [ ] Storage link (php artisan storage:link)
- [ ] Configure cron for queue worker
- [ ] Configure cron untuk email reminders
- [ ] Setup backup strategy
- [ ] SSL certificate
- [ ] Test email sending
- [ ] Test PDF generation

---

## MAINTENANCE & SUPPORT

### Regular Tasks

**Daily:**
- Monitor email queue
- Check for failed jobs
- Review error logs

**Weekly:**
- Database backup
- Check overdue invoices
- Review system performance

**Monthly:**
- Update dependencies (security patches)
- Clear old logs
- Archive old data (jika perlu)

### Backup Strategy

- Database backup: daily automatic
- File storage backup: weekly
- Keep last 30 days backups
- Monthly backup ke external storage

### Logging

- Laravel log level: error & critical (production)
- Log locations:
  - Application: storage/logs/laravel.log
  - Email: log all sent emails
  - PDF: log generation failures
- Setup log rotation (max 10 files, 100MB each)

---

## DOCUMENTATION REQUIREMENTS

### User Manual (untuk Staff & Superadmin)

Buat dokumentasi dalam Bahasa Indonesia:

**Bab 1: Pengenalan**
- Overview aplikasi
- Fitur utama
- Akses login

**Bab 2: Master Data**
- Cara manage categories
- Cara manage items
- Cara manage clients
- Cara manage users (Superadmin)

**Bab 3: Purchase Order**
- Cara membuat PO
- Cara approve PO (Superadmin)
- Cara input modal
- Cara input harga beli & jual

**Bab 4: Surat Jalan & Invoice**
- Cara generate Surat Jalan
- Cara generate Invoice
- Cara edit harga sebelum invoice
- Cara batch invoice

**Bab 5: Payment**
- Cara record payment
- Cara track overdue
- Payment history

**Bab 6: Pengeluaran**
- Cara input pengeluaran
- Kategori pengeluaran

**Bab 7: Laporan**
- Cara akses dashboard
- Cara generate laporan bulanan
- Cara export PDF & Excel

**Bab 8: FAQ & Troubleshooting**

### Technical Documentation (untuk Developer)

**README.md:**
- Installation steps
- Configuration
- Seeder commands
- Development workflow

**CODE_STRUCTURE.md:**
- Folder organization
- Naming conventions
- Code standards
- Database schema

**API_DOCUMENTATION.md** (jika ada API endpoints)

---

## NOTES & CONSIDERATIONS

### Currency Format
- Semua harga dalam IDR (Rupiah)
- Format: Rp 1.000.000 (dot sebagai thousand separator)
- Decimal: 2 digits (Rp 1.000.000,50)
- Database: store as decimal(15,2)

### Date Format
- Display: DD/MM/YYYY atau DD MMM YYYY
- Database: YYYY-MM-DD (standard MySQL date)
- Timezone: Asia/Jakarta (WIB)

### Number Format
- Quantity: bisa decimal (0.5 kg, 2.5 liter)
- Store as decimal(10,2)

### Status Color Coding

**Purchase Order:**
- draft: secondary (gray)
- pending_approval: warning (yellow)
- approved: success (green)
- rejected: danger (red)
- cancelled: dark (dark gray)
- completed: info (blue)

**Invoice:**
- unpaid: warning (yellow)
- partial: info (blue)
- paid: success (green)
- overdue: danger (red)

**Surat Jalan:**
- draft: secondary (gray)
- sent: info (blue)
- received: success (green)

### Notification Badge Numbers
- PO pending approval: count
- Overdue invoices: count
- Unpaid invoices: count

---

## GLOSSARY

- **PO:** Purchase Order
- **SJ:** Surat Jalan
- **COD:** Cash On Delivery (payment_terms = 0)
- **NET 7/14/30:** Payment dalam 7/14/30 hari setelah invoice date
- **COGS:** Cost of Goods Sold (harga beli)
- **Gross Profit:** Revenue - COGS
- **Net Profit:** Gross Profit - Expenses
- **Partial Payment:** Pembayaran sebagian (cicilan)
- **Overdue:** Invoice yang melewati due date
- **Credit Limit:** Batas maksimal outstanding per client

---

## CONTACT & SUPPORT

Untuk development questions atau support:
- Track di PROGRESS.md
- Dokumentasi code dengan comments
- Git commit messages yang jelas

---