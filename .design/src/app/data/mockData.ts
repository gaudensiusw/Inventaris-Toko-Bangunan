// Database Schema Types
export interface Category {
  id: string;
  name: string;
  description: string;
  sub_categories?: string[]; // Array of sub-category names
  created_at: string;
}

export interface Supplier {
  id: string;
  name: string;
  contact_person: string;
  phone: string;
  email: string;
  address: string;
  created_at: string;
}

export interface Product {
  id: string;
  sku: string;
  barcode?: string; // Auto-generated or manual barcode (EAN-13 format)
  name: string;
  brand: string; // Brand name (e.g., "Aries", "Sika", "Nippon Paint")
  category_id: string;
  sub_category?: string; // Sub-category (e.g., "Cat Kayu", "Cat Besi")
  supplier_id: string;
  description: string;
  image_url?: string; // Product image URL
  base_unit: string; // Smallest unit (e.g., "kg", "piece")
  units: UnitConversion[]; // Multi-level unit conversions
  current_stock: number; // Always in base_unit
  min_stock: number;
  max_stock: number;
  hpp: number; // Harga Pokok Penjualan (COGS) per base_unit
  selling_price: number; // per base_unit
  margin_percentage: number;
  tax_percentage: number;
  last_purchase_date?: string; // Last date this product was purchased
  last_purchase_price?: number; // Last purchase price (HPP)
  created_at: string;
  updated_at: string;
}

export interface UnitConversion {
  unit_name: string; // e.g., "sak", "palet", "ton"
  conversion_to_base: number; // How many base units in this unit (e.g., 1 sak = 50 kg)
  is_default_purchase?: boolean; // Default unit for purchasing
  is_default_sale?: boolean; // Default unit for selling
}

export interface StockTransaction {
  id: string;
  product_id: string;
  transaction_type: 'in' | 'out' | 'adjustment' | 'sale';
  quantity: number; // Always in base_unit
  unit_used: string; // Unit name used in transaction
  quantity_in_unit: number; // Quantity in the unit used
  unit_price: number;
  total_amount: number;
  reference_number: string;
  notes: string;
  created_by: string;
  created_at: string;
  // Supplier payment info (for 'in' transactions)
  supplier_id?: string;
  payment_terms_days?: number; // Payment terms in days (e.g., 30, 60, 90)
  due_date?: string; // Calculated due date for payment
  payment_status?: 'unpaid' | 'partial' | 'paid'; // Payment status to supplier
  amount_paid?: number; // Amount already paid
}

export interface AuditLog {
  id: string;
  entity_type: 'product' | 'stock' | 'price' | 'supplier';
  entity_id: string;
  action: 'create' | 'update' | 'delete' | 'adjust';
  field_changed: string;
  old_value: string;
  new_value: string;
  reason: string;
  changed_by: string;
  changed_at: string;
}

export interface StockNotification {
  id: string;
  product_id: string;
  notification_type: 'min_stock' | 'max_stock' | 'expiry' | 'reorder';
  message: string;
  severity: 'low' | 'medium' | 'high';
  is_read: boolean;
  created_at: string;
}

export interface Sale {
  id: string;
  sale_number: string;
  total_amount: number;
  payment_method: 'cash' | 'transfer' | 'credit';
  payment_status: 'paid' | 'pending' | 'partial';
  items: SaleItem[];
  customer_name?: string;
  created_by: string;
  created_at: string;
  due_date?: string; // For credit sales
  paid_amount?: number; // For partial payments
  cost_amount?: number; // Total cost (HPP) for profit calculation
}

export interface SaleItem {
  product_id: string;
  quantity: number;
  unit_price: number;
  total: number;
  cost_price?: number; // HPP per unit
}

export interface StockOpname {
  id: string;
  opname_number: string;
  opname_date: string;
  performed_by: string;
  approved_by?: string;
  status: 'draft' | 'pending_approval' | 'approved' | 'rejected';
  items: StockOpnameItem[];
  total_variance_value: number; // Total value of variances
  notes?: string;
  created_at: string;
  updated_at: string;
}

export interface StockOpnameItem {
  id: string;
  opname_id: string;
  product_id: string;
  system_stock: number; // Stock in system (base unit)
  physical_stock: number; // Actual physical count (base unit)
  variance: number; // Difference (physical - system)
  variance_percentage: number; // (variance / system_stock) * 100
  variance_value: number; // variance * hpp
  variance_type: 'match' | 'surplus' | 'shortage';
  reason?: string; // Reason for variance
  location?: string; // Storage location
  counted_by: string;
  notes?: string;
}

export interface MLPrediction {
  product_id: string;
  prediction_type: 'demand' | 'stock_duration';
  predicted_value: number;
  confidence: number;
  prediction_date: string;
  metadata: {
    historical_days: number;
    algorithm: string;
  };
}

export interface User {
  id: string;
  name: string;
  email: string;
  role: 'operator' | 'supervisor' | 'owner';
}

export interface Employee {
  id: string;
  employee_code: string;
  name: string;
  position: string;
  phone: string;
  email: string;
  address: string;
  join_date: string;
  status: 'active' | 'inactive';
  daily_salary: number; // Gaji pokok per hari
  bonus: number; // Bonus tetap
  created_at: string;
}

export interface Attendance {
  id: string;
  employee_id: string;
  date: string; // YYYY-MM-DD
  status: 'present' | 'absent' | 'sick' | 'leave' | 'holiday';
  check_in?: string; // Time of check in
  check_out?: string; // Time of check out
  notes?: string;
  created_by: string;
  created_at: string;
}

export interface Payroll {
  id: string;
  employee_id: string;
  period_start: string;
  period_end: string;
  total_days_present: number;
  base_salary: number; // daily_salary * total_days_present
  bonus: number;
  deductions: number;
  total_salary: number; // base_salary + bonus - deductions
  payment_status: 'pending' | 'paid';
  payment_date?: string;
  notes?: string;
  created_by: string;
  created_at: string;
}

export interface OperationalItem {
  id: string;
  item_code: string;
  name: string;
  category: 'packaging' | 'stationery' | 'cleaning' | 'utilities' | 'other';
  unit: string; // e.g., 'pcs', 'pack', 'roll', 'box'
  current_stock: number;
  min_stock: number;
  unit_price: number; // Price per unit
  last_purchase_date?: string;
  last_purchase_quantity?: number;
  supplier_name?: string;
  notes?: string;
  created_at: string;
  updated_at: string;
}

export interface OperationalExpense {
  id: string;
  item_id: string;
  item_name: string;
  transaction_type: 'purchase' | 'usage'; // Purchase = buy new stock, Usage = use from stock
  quantity: number;
  unit_price: number;
  total_amount: number;
  date: string;
  notes?: string;
  created_by: string;
  created_at: string;
}

export interface Customer {
  id: string;
  customer_code: string;
  name: string;
  phone: string;
  email?: string;
  address: string;
  customer_type: 'retail' | 'wholesale' | 'project'; // Type of customer
  credit_limit: number; // Maximum credit allowed
  payment_terms_days: number; // Default payment terms in days (e.g., 30, 60, 90)
  total_purchases: number; // Total lifetime purchases
  total_outstanding: number; // Current outstanding balance
  status: 'active' | 'inactive' | 'blocked'; // Customer status
  notes?: string;
  created_at: string;
}

export interface CustomerTransaction {
  id: string;
  transaction_number: string;
  customer_id: string;
  transaction_date: string;
  due_date: string; // Payment due date
  items: SaleItem[]; // Same as Sale items
  subtotal: number;
  tax_amount: number;
  total_amount: number;
  cost_amount: number; // Total cost (HPP) for profit calculation
  
  // Payment tracking
  payment_status: 'unpaid' | 'partial' | 'paid';
  payments: CustomerPayment[]; // Array of payments made
  total_paid: number; // Sum of all payments
  remaining_balance: number; // total_amount - total_paid
  
  // Metadata
  notes?: string;
  created_by: string;
  created_at: string;
  updated_at: string;
}

export interface CustomerPayment {
  id: string;
  payment_date: string;
  payment_method: 'cash' | 'transfer' | 'mixed'; // Mixed = cash + transfer
  cash_amount: number; // Amount paid in cash
  transfer_amount: number; // Amount paid via transfer
  total_amount: number; // cash_amount + transfer_amount
  bank_name?: string; // For transfer payments
  reference_number?: string; // Transfer reference number
  notes?: string;
  received_by: string; // User who received the payment
  created_at: string;
}

// Dummy Data
export const categories: Category[] = [
  {
    id: 'cat-1',
    name: 'Cement & Concrete',
    description: 'Cement, mortar, and concrete products',
    created_at: '2024-01-01T10:00:00Z',
  },
  {
    id: 'cat-2',
    name: 'Steel & Metal',
    description: 'Steel bars, metal sheets, and reinforcement',
    created_at: '2024-01-01T10:00:00Z',
  },
  {
    id: 'cat-3',
    name: 'Bricks & Blocks',
    description: 'Bricks, blocks, and masonry materials',
    created_at: '2024-01-01T10:00:00Z',
  },
  {
    id: 'cat-4',
    name: 'Paint & Coating',
    description: 'Paint, primer, and coating materials',
    sub_categories: ['Cat Kayu', 'Cat Besi', 'Cat Tembok', 'Cat Minyak', 'Cat Anti Karat'],
    created_at: '2024-01-01T10:00:00Z',
  },
  {
    id: 'cat-5',
    name: 'Plumbing',
    description: 'Pipes, fittings, and plumbing supplies',
    sub_categories: ['PVC Pipe', 'PPR Pipe', 'Galvanis Pipe', 'Fittings', 'Accessories'],
    created_at: '2024-01-01T10:00:00Z',
  },
  {
    id: 'cat-6',
    name: 'Electrical',
    description: 'Electrical wiring, switches, and components',
    sub_categories: ['Cable & Wire', 'MCB & Circuit Breaker', 'Switch & Socket', 'Conduit & Accessories'],
    created_at: '2024-01-01T10:00:00Z',
  },
];

export const suppliers: Supplier[] = [
  {
    id: 'sup-1',
    name: 'PT Semen Indonesia',
    contact_person: 'Budi Santoso',
    phone: '+62-21-5123456',
    email: 'budi@semenindonesia.co.id',
    address: 'Jakarta Selatan, DKI Jakarta',
    created_at: '2024-01-01T10:00:00Z',
  },
  {
    id: 'sup-2',
    name: 'CV Baja Sentosa',
    contact_person: 'Siti Nurhaliza',
    phone: '+62-31-7654321',
    email: 'siti@bajasentosa.com',
    address: 'Surabaya, Jawa Timur',
    created_at: '2024-01-01T10:00:00Z',
  },
  {
    id: 'sup-3',
    name: 'UD Bata Merah',
    contact_person: 'Ahmad Yani',
    phone: '+62-274-8887766',
    email: 'ahmad@batamerah.co.id',
    address: 'Yogyakarta, DIY',
    created_at: '2024-01-01T10:00:00Z',
  },
  {
    id: 'sup-4',
    name: 'PT Dulux Indonesia',
    contact_person: 'Linda Wijaya',
    phone: '+62-21-9998877',
    email: 'linda@dulux.co.id',
    address: 'Jakarta Barat, DKI Jakarta',
    created_at: '2024-01-01T10:00:00Z',
  },
];

export const products: Product[] = [
  {
    id: 'prod-1',
    sku: 'CEM-001',
    name: 'Semen Portland - Gresik',
    brand: 'Aries',
    category_id: 'cat-1',
    supplier_id: 'sup-1',
    description: 'Premium Portland cement, 50kg per sack',
    image_url: 'https://example.com/images/semen-portland.jpg',
    base_unit: 'kg',
    units: [
      { unit_name: 'sack', conversion_to_base: 50, is_default_purchase: true, is_default_sale: true },
      { unit_name: 'palet', conversion_to_base: 2500 },
    ],
    current_stock: 850,
    min_stock: 200,
    max_stock: 2000,
    hpp: 62000,
    selling_price: 75000,
    margin_percentage: 15,
    tax_percentage: 11,
    last_purchase_date: '2026-03-01T08:00:00Z',
    last_purchase_price: 62000,
    created_at: '2024-01-15T08:00:00Z',
    updated_at: '2026-03-07T14:30:00Z',
  },
  {
    id: 'prod-2',
    sku: 'STL-002',
    name: 'Besi Beton 10mm x 12m',
    brand: 'Sika',
    category_id: 'cat-2',
    supplier_id: 'sup-2',
    description: 'Steel rebar, 10mm diameter, 12m length',
    base_unit: 'piece',
    units: [
      { unit_name: 'piece', conversion_to_base: 1, is_default_purchase: true, is_default_sale: true },
    ],
    current_stock: 180,
    min_stock: 50,
    max_stock: 500,
    hpp: 85000,
    selling_price: 105000,
    margin_percentage: 18,
    tax_percentage: 11,
    last_purchase_date: '2026-03-06T16:20:00Z',
    last_purchase_price: 85000,
    created_at: '2024-02-01T09:00:00Z',
    updated_at: '2026-03-06T16:20:00Z',
  },
  {
    id: 'prod-3',
    sku: 'BRK-003',
    name: 'Bata Merah Press',
    brand: 'Nippon Paint',
    category_id: 'cat-3',
    supplier_id: 'sup-3',
    description: 'Pressed red brick, standard size',
    base_unit: 'piece',
    units: [
      { unit_name: 'piece', conversion_to_base: 1, is_default_purchase: true, is_default_sale: true },
      { unit_name: 'palet', conversion_to_base: 1000 },
    ],
    current_stock: 15000,
    min_stock: 5000,
    max_stock: 30000,
    hpp: 950,
    selling_price: 1200,
    margin_percentage: 20,
    tax_percentage: 11,
    last_purchase_date: '2026-03-02T09:00:00Z',
    last_purchase_price: 950,
    created_at: '2024-01-20T10:00:00Z',
    updated_at: '2026-03-05T11:15:00Z',
  },
  {
    id: 'prod-4',
    sku: 'PNT-004',
    name: 'Cat Tembok Weathershield',
    brand: 'Dulux',
    category_id: 'cat-4',
    sub_category: 'Cat Tembok',
    supplier_id: 'sup-4',
    description: 'Interior wall paint, white color, 25kg bucket',
    image_url: 'https://images.unsplash.com/photo-1589939705384-5185137a7f0f?w=400',
    base_unit: 'kg',
    units: [
      { unit_name: 'bucket', conversion_to_base: 25, is_default_purchase: true, is_default_sale: true },
    ],
    current_stock: 95,
    min_stock: 30,
    max_stock: 200,
    hpp: 580000,
    selling_price: 725000,
    margin_percentage: 20,
    tax_percentage: 11,
    last_purchase_date: '2026-03-03T14:00:00Z',
    last_purchase_price: 580000,
    created_at: '2024-02-10T08:30:00Z',
    updated_at: '2026-03-07T09:45:00Z',
  },
  {
    id: 'prod-5',
    sku: 'PLB-005',
    name: 'Pipa PVC 3" x 4m',
    brand: 'Aries',
    category_id: 'cat-5',
    supplier_id: 'sup-2',
    description: 'PVC pipe, 3 inch diameter, 4 meter length',
    base_unit: 'piece',
    units: [
      { unit_name: 'piece', conversion_to_base: 1, is_default_purchase: true, is_default_sale: true },
    ],
    current_stock: 45,
    min_stock: 100,
    max_stock: 400,
    hpp: 48000,
    selling_price: 62000,
    margin_percentage: 22,
    tax_percentage: 11,
    last_purchase_date: '2026-03-08T10:00:00Z',
    last_purchase_price: 48000,
    created_at: '2024-02-15T13:00:00Z',
    updated_at: '2026-03-08T10:00:00Z',
  },
  {
    id: 'prod-6',
    sku: 'CEM-002',
    name: 'Semen Putih - Holcim',
    brand: 'Holcim',
    category_id: 'cat-1',
    supplier_id: 'sup-1',
    description: 'White cement, 40kg per sack',
    base_unit: 'kg',
    units: [
      { unit_name: 'sack', conversion_to_base: 40, is_default_purchase: true, is_default_sale: true },
      { unit_name: 'palet', conversion_to_base: 2000 },
    ],
    current_stock: 320,
    min_stock: 100,
    max_stock: 800,
    hpp: 125000,
    selling_price: 155000,
    margin_percentage: 18,
    tax_percentage: 11,
    last_purchase_date: '2024-02-20T14:00:00Z',
    last_purchase_price: 125000,
    created_at: '2024-02-20T14:00:00Z',
    updated_at: '2026-03-07T15:30:00Z',
  },
];

export const stockTransactions: StockTransaction[] = [
  {
    id: 'txn-1',
    product_id: 'prod-1',
    transaction_type: 'in',
    quantity: 500,
    unit_used: 'palet',
    quantity_in_unit: 1,
    unit_price: 62000,
    total_amount: 31000000,
    reference_number: 'PO-2026-001',
    notes: 'Restock from PT Semen Indonesia',
    created_by: 'operator-1',
    created_at: '2026-03-01T08:00:00Z',
    supplier_id: 'sup-1',
    payment_terms_days: 30,
    due_date: '2026-03-31T08:00:00Z',
    payment_status: 'unpaid',
    amount_paid: 0,
  },
  {
    id: 'txn-2',
    product_id: 'prod-1',
    transaction_type: 'out',
    quantity: 150,
    unit_used: 'sack',
    quantity_in_unit: 150,
    unit_price: 75000,
    total_amount: 11250000,
    reference_number: 'SO-2026-001',
    notes: 'Sale to PT Konstruksi ABC',
    created_by: 'operator-1',
    created_at: '2026-03-05T10:30:00Z',
  },
  {
    id: 'txn-3',
    product_id: 'prod-3',
    transaction_type: 'in',
    quantity: 5000,
    unit_used: 'palet',
    quantity_in_unit: 5,
    unit_price: 950,
    total_amount: 4750000,
    reference_number: 'PO-2026-002',
    notes: 'Purchase from UD Bata Merah',
    created_by: 'operator-2',
    created_at: '2026-03-02T09:00:00Z',
    supplier_id: 'sup-3',
    payment_terms_days: 60,
    due_date: '2026-05-01T09:00:00Z',
    payment_status: 'unpaid',
    amount_paid: 0,
  },
  {
    id: 'txn-4',
    product_id: 'prod-4',
    transaction_type: 'adjustment',
    quantity: -5,
    unit_used: 'bucket',
    quantity_in_unit: 5,
    unit_price: 580000,
    total_amount: -2900000,
    reference_number: 'ADJ-2026-001',
    notes: 'Damaged goods write-off',
    created_by: 'supervisor-1',
    created_at: '2026-03-03T14:00:00Z',
  },
  {
    id: 'txn-5',
    product_id: 'prod-2',
    transaction_type: 'sale',
    quantity: 50,
    unit_used: 'piece',
    quantity_in_unit: 50,
    unit_price: 105000,
    total_amount: 5250000,
    reference_number: 'INV-2026-012',
    notes: 'Cash sale',
    created_by: 'operator-1',
    created_at: '2026-03-07T11:00:00Z',
  },
  {
    id: 'txn-6',
    product_id: 'prod-2',
    transaction_type: 'in',
    quantity: 100,
    unit_used: 'piece',
    quantity_in_unit: 100,
    unit_price: 85000,
    total_amount: 8500000,
    reference_number: 'PO-2026-003',
    notes: 'Purchase steel rebar from CV Baja Sentosa',
    created_by: 'operator-1',
    created_at: '2026-03-10T14:00:00Z',
    supplier_id: 'sup-2',
    payment_terms_days: 45,
    due_date: '2026-04-24T14:00:00Z',
    payment_status: 'unpaid',
    amount_paid: 0,
  },
  {
    id: 'txn-7',
    product_id: 'prod-4',
    transaction_type: 'in',
    quantity: 50,
    unit_used: 'bucket',
    quantity_in_unit: 2,
    unit_price: 580000,
    total_amount: 29000000,
    reference_number: 'PO-2026-004',
    notes: 'Paint restock from PT Dulux',
    created_by: 'operator-2',
    created_at: '2026-02-15T10:00:00Z',
    supplier_id: 'sup-4',
    payment_terms_days: 30,
    due_date: '2026-03-17T10:00:00Z',
    payment_status: 'partial',
    amount_paid: 15000000,
  },
  {
    id: 'txn-8',
    product_id: 'prod-5',
    transaction_type: 'in',
    quantity: 80,
    unit_used: 'piece',
    quantity_in_unit: 80,
    unit_price: 48000,
    total_amount: 3840000,
    reference_number: 'PO-2026-005',
    notes: 'PVC pipe purchase',
    created_by: 'operator-1',
    created_at: '2026-03-08T10:00:00Z',
    supplier_id: 'sup-2',
    payment_terms_days: 30,
    due_date: '2026-04-07T10:00:00Z',
    payment_status: 'unpaid',
    amount_paid: 0,
  },
  {
    id: 'txn-9',
    product_id: 'prod-1',
    transaction_type: 'in',
    quantity: 1000,
    unit_used: 'palet',
    quantity_in_unit: 2,
    unit_price: 62000,
    total_amount: 62000000,
    reference_number: 'PO-2026-006',
    notes: 'Large cement order for construction project',
    created_by: 'operator-2',
    created_at: '2026-02-20T08:00:00Z',
    supplier_id: 'sup-1',
    payment_terms_days: 60,
    due_date: '2026-04-21T08:00:00Z',
    payment_status: 'unpaid',
    amount_paid: 0,
  },
  {
    id: 'txn-10',
    product_id: 'prod-6',
    transaction_type: 'in',
    quantity: 200,
    unit_used: 'sack',
    quantity_in_unit: 5,
    unit_price: 125000,
    total_amount: 25000000,
    reference_number: 'PO-2026-007',
    notes: 'White cement purchase',
    created_by: 'operator-1',
    created_at: '2026-03-05T09:00:00Z',
    supplier_id: 'sup-1',
    payment_terms_days: 30,
    due_date: '2026-04-04T09:00:00Z',
    payment_status: 'unpaid',
    amount_paid: 0,
  },
];

export const auditLogs: AuditLog[] = [
  {
    id: 'log-1',
    entity_type: 'price',
    entity_id: 'prod-1',
    action: 'update',
    field_changed: 'selling_price',
    old_value: '72000',
    new_value: '75000',
    reason: 'Market price adjustment due to fuel cost increase',
    changed_by: 'Owner - John Doe',
    changed_at: '2026-03-01T15:30:00Z',
  },
  {
    id: 'log-2',
    entity_type: 'stock',
    entity_id: 'prod-4',
    action: 'adjust',
    field_changed: 'current_stock',
    old_value: '100',
    new_value: '95',
    reason: 'Physical count adjustment - 5 buckets damaged',
    changed_by: 'Supervisor - Jane Smith',
    changed_at: '2026-03-03T14:00:00Z',
  },
  {
    id: 'log-3',
    entity_type: 'product',
    entity_id: 'prod-5',
    action: 'update',
    field_changed: 'min_stock',
    old_value: '50',
    new_value: '100',
    reason: 'Increased minimum threshold due to higher demand',
    changed_by: 'Supervisor - Jane Smith',
    changed_at: '2026-03-04T10:15:00Z',
  },
  {
    id: 'log-4',
    entity_type: 'price',
    entity_id: 'prod-2',
    action: 'update',
    field_changed: 'hpp',
    old_value: '82000',
    new_value: '85000',
    reason: 'Supplier price increase from CV Baja Sentosa',
    changed_by: 'Owner - John Doe',
    changed_at: '2026-03-06T16:20:00Z',
  },
  {
    id: 'log-5',
    entity_type: 'product',
    entity_id: 'prod-6',
    action: 'create',
    field_changed: 'all',
    old_value: '',
    new_value: 'New product added',
    reason: 'New product line addition',
    changed_by: 'Supervisor - Jane Smith',
    changed_at: '2024-02-20T14:00:00Z',
  },
];

export const stockNotifications: StockNotification[] = [
  {
    id: 'notif-1',
    product_id: 'prod-5',
    notification_type: 'min_stock',
    message: 'Pipa PVC 3" x 4m is below minimum stock level (45/100)',
    severity: 'high',
    is_read: false,
    created_at: '2026-03-08T10:00:00Z',
  },
  {
    id: 'notif-2',
    product_id: 'prod-3',
    notification_type: 'max_stock',
    message: 'Bata Merah Press is approaching maximum stock level (15000/30000)',
    severity: 'low',
    is_read: false,
    created_at: '2026-03-07T08:00:00Z',
  },
  {
    id: 'notif-3',
    product_id: 'prod-1',
    notification_type: 'reorder',
    message: 'Semen Portland - Gresik should be reordered soon',
    severity: 'medium',
    is_read: true,
    created_at: '2026-03-05T14:00:00Z',
  },
];

export const sales: Sale[] = [
  {
    id: 'sale-1',
    sale_number: 'INV-2026-010',
    total_amount: 12075000,
    payment_method: 'transfer',
    payment_status: 'paid',
    customer_name: 'PT Konstruksi Jaya',
    items: [
      { product_id: 'prod-1', quantity: 150, unit_price: 75000, total: 11250000, cost_price: 62000 },
      { product_id: 'prod-4', quantity: 1, unit_price: 725000, total: 725000, cost_price: 580000 },
      { product_id: 'prod-5', quantity: 2, unit_price: 62000, total: 124000, cost_price: 48000 },
    ],
    cost_amount: 9876000, // (150*62000) + (1*580000) + (2*48000)
    created_by: 'operator-1',
    created_at: '2026-03-05T10:30:00Z',
  },
  {
    id: 'sale-2',
    sale_number: 'INV-2026-011',
    total_amount: 3600000,
    payment_method: 'cash',
    payment_status: 'paid',
    items: [
      { product_id: 'prod-3', quantity: 3000, unit_price: 1200, total: 3600000, cost_price: 950 },
    ],
    cost_amount: 2850000, // 3000*950
    created_by: 'operator-2',
    created_at: '2026-03-06T14:15:00Z',
  },
  {
    id: 'sale-3',
    sale_number: 'INV-2026-012',
    total_amount: 6605000,
    payment_method: 'credit',
    payment_status: 'pending',
    customer_name: 'UD Maju Bersama',
    items: [
      { product_id: 'prod-2', quantity: 50, unit_price: 105000, total: 5250000, cost_price: 85000 },
      { product_id: 'prod-6', quantity: 8, unit_price: 155000, total: 1240000, cost_price: 125000 },
      { product_id: 'prod-5', quantity: 2, unit_price: 62000, total: 124000, cost_price: 48000 },
    ],
    cost_amount: 5346000, // (50*85000) + (8*125000) + (2*48000)
    due_date: '2026-03-22T00:00:00Z', // 15 days from sale
    created_by: 'operator-1',
    created_at: '2026-03-07T11:00:00Z',
  },
  {
    id: 'sale-4',
    sale_number: 'INV-2026-013',
    total_amount: 1450000,
    payment_method: 'cash',
    payment_status: 'paid',
    items: [
      { product_id: 'prod-4', quantity: 2, unit_price: 725000, total: 1450000, cost_price: 580000 },
    ],
    cost_amount: 1160000, // 2*580000
    created_by: 'operator-1',
    created_at: '2026-03-08T09:20:00Z',
  },
  {
    id: 'sale-5',
    sale_number: 'INV-2026-014',
    total_amount: 8950000,
    payment_method: 'credit',
    payment_status: 'pending',
    customer_name: 'CV Bangun Sejahtera',
    items: [
      { product_id: 'prod-1', quantity: 100, unit_price: 75000, total: 7500000, cost_price: 62000 },
      { product_id: 'prod-2', quantity: 10, unit_price: 105000, total: 1050000, cost_price: 85000 },
      { product_id: 'prod-3', quantity: 200, unit_price: 1200, total: 240000, cost_price: 950 },
      { product_id: 'prod-5', quantity: 3, unit_price: 62000, total: 186000, cost_price: 48000 },
    ],
    cost_amount: 7284000, // (100*62000) + (10*85000) + (200*950) + (3*48000)
    due_date: '2026-03-25T00:00:00Z', // 17 days from sale
    created_by: 'operator-2',
    created_at: '2026-03-08T13:45:00Z',
  },
  {
    id: 'sale-6',
    sale_number: 'INV-2026-015',
    total_amount: 4650000,
    payment_method: 'credit',
    payment_status: 'partial',
    customer_name: 'Toko Bangunan Sentosa',
    items: [
      { product_id: 'prod-6', quantity: 30, unit_price: 155000, total: 4650000, cost_price: 125000 },
    ],
    cost_amount: 3750000, // 30*125000
    paid_amount: 2000000,
    due_date: '2026-03-18T00:00:00Z', // 10 days from sale - OVERDUE SOON
    created_by: 'operator-1',
    created_at: '2026-03-08T15:30:00Z',
  },
  {
    id: 'sale-7',
    sale_number: 'INV-2026-016',
    total_amount: 2480000,
    payment_method: 'cash',
    payment_status: 'paid',
    customer_name: 'Kontraktor Wijaya',
    items: [
      { product_id: 'prod-3', quantity: 2000, unit_price: 1200, total: 2400000, cost_price: 950 },
      { product_id: 'prod-5', quantity: 1, unit_price: 62000, total: 62000, cost_price: 48000 },
    ],
    cost_amount: 1948000, // (2000*950) + (1*48000)
    created_by: 'operator-2',
    created_at: '2026-03-09T10:00:00Z',
  },
  {
    id: 'sale-8',
    sale_number: 'INV-2026-017',
    total_amount: 15500000,
    payment_method: 'transfer',
    payment_status: 'paid',
    customer_name: 'PT Pembangunan Modern',
    items: [
      { product_id: 'prod-1', quantity: 200, unit_price: 75000, total: 15000000, cost_price: 62000 },
      { product_id: 'prod-5', quantity: 8, unit_price: 62000, total: 496000, cost_price: 48000 },
    ],
    cost_amount: 12784000, // (200*62000) + (8*48000)
    created_by: 'operator-1',
    created_at: '2026-03-10T08:15:00Z',
  },
  {
    id: 'sale-9',
    sale_number: 'INV-2026-018',
    total_amount: 3625000,
    payment_method: 'credit',
    payment_status: 'pending',
    customer_name: 'UD Karya Mandiri',
    items: [
      { product_id: 'prod-4', quantity: 5, unit_price: 725000, total: 3625000, cost_price: 580000 },
    ],
    cost_amount: 2900000, // 5*580000
    due_date: '2026-03-15T00:00:00Z', // OVERDUE (5 days ago)
    created_by: 'operator-2',
    created_at: '2026-02-28T14:20:00Z',
  },
  {
    id: 'sale-10',
    sale_number: 'INV-2026-019',
    total_amount: 5400000,
    payment_method: 'cash',
    payment_status: 'paid',
    items: [
      { product_id: 'prod-3', quantity: 4500, unit_price: 1200, total: 5400000, cost_price: 950 },
    ],
    cost_amount: 4275000, // 4500*950
    created_by: 'operator-1',
    created_at: '2026-03-11T11:30:00Z',
  },
  {
    id: 'sale-11',
    sale_number: 'INV-2026-020',
    total_amount: 7750000,
    payment_method: 'credit',
    payment_status: 'pending',
    customer_name: 'CV Sukses Makmur',
    items: [
      { product_id: 'prod-2', quantity: 50, unit_price: 105000, total: 5250000, cost_price: 85000 },
      { product_id: 'prod-4', quantity: 3, unit_price: 725000, total: 2175000, cost_price: 580000 },
      { product_id: 'prod-5', quantity: 5, unit_price: 62000, total: 310000, cost_price: 48000 },
    ],
    cost_amount: 6230000, // (50*85000) + (3*580000) + (5*48000)
    due_date: '2026-03-14T00:00:00Z', // OVERDUE (3 days ago)
    created_by: 'operator-1',
    created_at: '2026-02-27T16:00:00Z',
  },
  {
    id: 'sale-12',
    sale_number: 'INV-2026-021',
    total_amount: 9300000,
    payment_method: 'transfer',
    payment_status: 'paid',
    customer_name: 'Toko Material Jaya',
    items: [
      { product_id: 'prod-1', quantity: 120, unit_price: 75000, total: 9000000, cost_price: 62000 },
      { product_id: 'prod-6', quantity: 2, unit_price: 155000, total: 310000, cost_price: 125000 },
    ],
    cost_amount: 7690000, // (120*62000) + (2*125000)
    created_by: 'operator-2',
    created_at: '2026-03-12T09:45:00Z',
  },
];

export const mlPredictions: MLPrediction[] = [
  {
    product_id: 'prod-1',
    prediction_type: 'demand',
    predicted_value: 320, // Predicted sales in next 7 days
    confidence: 0.87,
    prediction_date: '2026-03-08T00:00:00Z',
    metadata: {
      historical_days: 90,
      algorithm: 'ARIMA',
    },
  },
  {
    product_id: 'prod-1',
    prediction_type: 'stock_duration',
    predicted_value: 18, // Days until stock runs out
    confidence: 0.82,
    prediction_date: '2026-03-08T00:00:00Z',
    metadata: {
      historical_days: 60,
      algorithm: 'Linear Regression',
    },
  },
  {
    product_id: 'prod-2',
    prediction_type: 'demand',
    predicted_value: 85,
    confidence: 0.79,
    prediction_date: '2026-03-08T00:00:00Z',
    metadata: {
      historical_days: 90,
      algorithm: 'ARIMA',
    },
  },
  {
    product_id: 'prod-2',
    prediction_type: 'stock_duration',
    predicted_value: 14,
    confidence: 0.75,
    prediction_date: '2026-03-08T00:00:00Z',
    metadata: {
      historical_days: 60,
      algorithm: 'Linear Regression',
    },
  },
  {
    product_id: 'prod-3',
    prediction_type: 'demand',
    predicted_value: 4200,
    confidence: 0.91,
    prediction_date: '2026-03-08T00:00:00Z',
    metadata: {
      historical_days: 90,
      algorithm: 'ARIMA',
    },
  },
  {
    product_id: 'prod-3',
    prediction_type: 'stock_duration',
    predicted_value: 25,
    confidence: 0.88,
    prediction_date: '2026-03-08T00:00:00Z',
    metadata: {
      historical_days: 60,
      algorithm: 'Linear Regression',
    },
  },
  {
    product_id: 'prod-4',
    prediction_type: 'demand',
    predicted_value: 22,
    confidence: 0.73,
    prediction_date: '2026-03-08T00:00:00Z',
    metadata: {
      historical_days: 90,
      algorithm: 'ARIMA',
    },
  },
  {
    product_id: 'prod-4',
    prediction_type: 'stock_duration',
    predicted_value: 30,
    confidence: 0.69,
    prediction_date: '2026-03-08T00:00:00Z',
    metadata: {
      historical_days: 60,
      algorithm: 'Linear Regression',
    },
  },
  {
    product_id: 'prod-5',
    prediction_type: 'demand',
    predicted_value: 15,
    confidence: 0.68,
    prediction_date: '2026-03-08T00:00:00Z',
    metadata: {
      historical_days: 90,
      algorithm: 'ARIMA',
    },
  },
  {
    product_id: 'prod-5',
    prediction_type: 'stock_duration',
    predicted_value: 21,
    confidence: 0.71,
    prediction_date: '2026-03-08T00:00:00Z',
    metadata: {
      historical_days: 60,
      algorithm: 'Linear Regression',
    },
  },
];

export const users: User[] = [
  {
    id: 'user-1',
    name: 'Andi Operator',
    email: 'operator@tokobangunan.com',
    role: 'operator',
  },
  {
    id: 'user-2',
    name: 'Siti Supervisor',
    email: 'supervisor@tokobangunan.com',
    role: 'supervisor',
  },
  {
    id: 'user-3',
    name: 'Budi Owner',
    email: 'owner@tokobangunan.com',
    role: 'owner',
  },
];

export const employees: Employee[] = [
  {
    id: 'emp-1',
    employee_code: 'EMP-001',
    name: 'Andi Setiawan',
    position: 'Kasir',
    phone: '+62-812-3456-7890',
    email: 'andi.setiawan@tokobangunan.com',
    address: 'Jl. Merdeka No. 123, Jakarta',
    join_date: '2024-01-15T00:00:00Z',
    status: 'active',
    daily_salary: 100000,
    bonus: 500000,
    created_at: '2024-01-15T08:00:00Z',
  },
  {
    id: 'emp-2',
    employee_code: 'EMP-002',
    name: 'Siti Rahayu',
    position: 'Staff Gudang',
    phone: '+62-813-9876-5432',
    email: 'siti.rahayu@tokobangunan.com',
    address: 'Jl. Sudirman No. 45, Jakarta',
    join_date: '2024-02-01T00:00:00Z',
    status: 'active',
    daily_salary: 90000,
    bonus: 300000,
    created_at: '2024-02-01T08:00:00Z',
  },
  {
    id: 'emp-3',
    employee_code: 'EMP-003',
    name: 'Budi Santoso',
    position: 'Driver',
    phone: '+62-815-1234-5678',
    email: 'budi.santoso@tokobangunan.com',
    address: 'Jl. Gatot Subroto No. 78, Jakarta',
    join_date: '2024-01-20T00:00:00Z',
    status: 'active',
    daily_salary: 120000,
    bonus: 400000,
    created_at: '2024-01-20T08:00:00Z',
  },
  {
    id: 'emp-4',
    employee_code: 'EMP-004',
    name: 'Dewi Lestari',
    position: 'Admin',
    phone: '+62-817-2468-1357',
    email: 'dewi.lestari@tokobangunan.com',
    address: 'Jl. Thamrin No. 90, Jakarta',
    join_date: '2024-03-01T00:00:00Z',
    status: 'active',
    daily_salary: 95000,
    bonus: 350000,
    created_at: '2024-03-01T08:00:00Z',
  },
  {
    id: 'emp-5',
    employee_code: 'EMP-005',
    name: 'Ahmad Fauzi',
    position: 'Staff Gudang',
    phone: '+62-818-9753-8642',
    email: 'ahmad.fauzi@tokobangunan.com',
    address: 'Jl. Rasuna Said No. 34, Jakarta',
    join_date: '2024-02-15T00:00:00Z',
    status: 'inactive',
    daily_salary: 90000,
    bonus: 0,
    created_at: '2024-02-15T08:00:00Z',
  },
];

export const attendances: Attendance[] = [
  // Andi Setiawan (emp-1) - March 2026
  { id: 'att-1', employee_id: 'emp-1', date: '2026-03-01', status: 'present', check_in: '08:00', check_out: '17:00', created_by: 'supervisor-1', created_at: '2026-03-01T08:00:00Z' },
  { id: 'att-2', employee_id: 'emp-1', date: '2026-03-02', status: 'present', check_in: '08:05', check_out: '17:05', created_by: 'supervisor-1', created_at: '2026-03-02T08:05:00Z' },
  { id: 'att-3', employee_id: 'emp-1', date: '2026-03-03', status: 'present', check_in: '07:55', check_out: '17:00', created_by: 'supervisor-1', created_at: '2026-03-03T07:55:00Z' },
  { id: 'att-4', employee_id: 'emp-1', date: '2026-03-04', status: 'present', check_in: '08:00', check_out: '17:00', created_by: 'supervisor-1', created_at: '2026-03-04T08:00:00Z' },
  { id: 'att-5', employee_id: 'emp-1', date: '2026-03-05', status: 'present', check_in: '08:10', check_out: '17:10', created_by: 'supervisor-1', created_at: '2026-03-05T08:10:00Z' },
  { id: 'att-6', employee_id: 'emp-1', date: '2026-03-06', status: 'present', check_in: '08:00', check_out: '17:00', created_by: 'supervisor-1', created_at: '2026-03-06T08:00:00Z' },
  { id: 'att-7', employee_id: 'emp-1', date: '2026-03-07', status: 'present', check_in: '08:00', check_out: '17:00', created_by: 'supervisor-1', created_at: '2026-03-07T08:00:00Z' },
  { id: 'att-8', employee_id: 'emp-1', date: '2026-03-08', status: 'holiday', notes: 'Weekend', created_by: 'system', created_at: '2026-03-08T00:00:00Z' },
  { id: 'att-9', employee_id: 'emp-1', date: '2026-03-09', status: 'holiday', notes: 'Weekend', created_by: 'system', created_at: '2026-03-09T00:00:00Z' },
  { id: 'att-10', employee_id: 'emp-1', date: '2026-03-10', status: 'present', check_in: '08:00', check_out: '17:00', created_by: 'supervisor-1', created_at: '2026-03-10T08:00:00Z' },
  { id: 'att-11', employee_id: 'emp-1', date: '2026-03-11', status: 'present', check_in: '08:00', check_out: '17:00', created_by: 'supervisor-1', created_at: '2026-03-11T08:00:00Z' },
  { id: 'att-12', employee_id: 'emp-1', date: '2026-03-12', status: 'sick', notes: 'Flu', created_by: 'supervisor-1', created_at: '2026-03-12T08:00:00Z' },
  { id: 'att-13', employee_id: 'emp-1', date: '2026-03-13', status: 'present', check_in: '08:00', check_out: '17:00', created_by: 'supervisor-1', created_at: '2026-03-13T08:00:00Z' },
  { id: 'att-14', employee_id: 'emp-1', date: '2026-03-14', status: 'present', check_in: '08:00', check_out: '17:00', created_by: 'supervisor-1', created_at: '2026-03-14T08:00:00Z' },

  // Siti Rahayu (emp-2) - March 2026
  { id: 'att-15', employee_id: 'emp-2', date: '2026-03-01', status: 'present', check_in: '08:00', check_out: '17:00', created_by: 'supervisor-1', created_at: '2026-03-01T08:00:00Z' },
  { id: 'att-16', employee_id: 'emp-2', date: '2026-03-02', status: 'present', check_in: '08:00', check_out: '17:00', created_by: 'supervisor-1', created_at: '2026-03-02T08:00:00Z' },
  { id: 'att-17', employee_id: 'emp-2', date: '2026-03-03', status: 'absent', notes: 'Tidak ada keterangan', created_by: 'supervisor-1', created_at: '2026-03-03T08:00:00Z' },
  { id: 'att-18', employee_id: 'emp-2', date: '2026-03-04', status: 'present', check_in: '08:00', check_out: '17:00', created_by: 'supervisor-1', created_at: '2026-03-04T08:00:00Z' },
  { id: 'att-19', employee_id: 'emp-2', date: '2026-03-05', status: 'present', check_in: '08:00', check_out: '17:00', created_by: 'supervisor-1', created_at: '2026-03-05T08:00:00Z' },
  { id: 'att-20', employee_id: 'emp-2', date: '2026-03-06', status: 'present', check_in: '08:00', check_out: '17:00', created_by: 'supervisor-1', created_at: '2026-03-06T08:00:00Z' },
  { id: 'att-21', employee_id: 'emp-2', date: '2026-03-07', status: 'present', check_in: '08:00', check_out: '17:00', created_by: 'supervisor-1', created_at: '2026-03-07T08:00:00Z' },
  { id: 'att-22', employee_id: 'emp-2', date: '2026-03-10', status: 'present', check_in: '08:00', check_out: '17:00', created_by: 'supervisor-1', created_at: '2026-03-10T08:00:00Z' },
  { id: 'att-23', employee_id: 'emp-2', date: '2026-03-11', status: 'present', check_in: '08:00', check_out: '17:00', created_by: 'supervisor-1', created_at: '2026-03-11T08:00:00Z' },
  { id: 'att-24', employee_id: 'emp-2', date: '2026-03-12', status: 'present', check_in: '08:00', check_out: '17:00', created_by: 'supervisor-1', created_at: '2026-03-12T08:00:00Z' },

  // Budi Santoso (emp-3) - March 2026
  { id: 'att-25', employee_id: 'emp-3', date: '2026-03-01', status: 'present', check_in: '07:00', check_out: '16:00', created_by: 'supervisor-1', created_at: '2026-03-01T07:00:00Z' },
  { id: 'att-26', employee_id: 'emp-3', date: '2026-03-02', status: 'present', check_in: '07:00', check_out: '16:00', created_by: 'supervisor-1', created_at: '2026-03-02T07:00:00Z' },
  { id: 'att-27', employee_id: 'emp-3', date: '2026-03-03', status: 'present', check_in: '07:00', check_out: '16:00', created_by: 'supervisor-1', created_at: '2026-03-03T07:00:00Z' },
  { id: 'att-28', employee_id: 'emp-3', date: '2026-03-04', status: 'present', check_in: '07:00', check_out: '16:00', created_by: 'supervisor-1', created_at: '2026-03-04T07:00:00Z' },
  { id: 'att-29', employee_id: 'emp-3', date: '2026-03-05', status: 'present', check_in: '07:00', check_out: '16:00', created_by: 'supervisor-1', created_at: '2026-03-05T07:00:00Z' },
  { id: 'att-30', employee_id: 'emp-3', date: '2026-03-06', status: 'present', check_in: '07:00', check_out: '16:00', created_by: 'supervisor-1', created_at: '2026-03-06T07:00:00Z' },
  { id: 'att-31', employee_id: 'emp-3', date: '2026-03-07', status: 'leave', notes: 'Cuti tahunan', created_by: 'supervisor-1', created_at: '2026-03-07T08:00:00Z' },
  { id: 'att-32', employee_id: 'emp-3', date: '2026-03-10', status: 'present', check_in: '07:00', check_out: '16:00', created_by: 'supervisor-1', created_at: '2026-03-10T07:00:00Z' },
  { id: 'att-33', employee_id: 'emp-3', date: '2026-03-11', status: 'present', check_in: '07:00', check_out: '16:00', created_by: 'supervisor-1', created_at: '2026-03-11T07:00:00Z' },
  { id: 'att-34', employee_id: 'emp-3', date: '2026-03-12', status: 'present', check_in: '07:00', check_out: '16:00', created_by: 'supervisor-1', created_at: '2026-03-12T07:00:00Z' },

  // Dewi Lestari (emp-4) - March 2026
  { id: 'att-35', employee_id: 'emp-4', date: '2026-03-01', status: 'present', check_in: '08:00', check_out: '17:00', created_by: 'supervisor-1', created_at: '2026-03-01T08:00:00Z' },
  { id: 'att-36', employee_id: 'emp-4', date: '2026-03-02', status: 'present', check_in: '08:00', check_out: '17:00', created_by: 'supervisor-1', created_at: '2026-03-02T08:00:00Z' },
  { id: 'att-37', employee_id: 'emp-4', date: '2026-03-03', status: 'present', check_in: '08:00', check_out: '17:00', created_by: 'supervisor-1', created_at: '2026-03-03T08:00:00Z' },
  { id: 'att-38', employee_id: 'emp-4', date: '2026-03-04', status: 'present', check_in: '08:00', check_out: '17:00', created_by: 'supervisor-1', created_at: '2026-03-04T08:00:00Z' },
  { id: 'att-39', employee_id: 'emp-4', date: '2026-03-05', status: 'present', check_in: '08:00', check_out: '17:00', created_by: 'supervisor-1', created_at: '2026-03-05T08:00:00Z' },
  { id: 'att-40', employee_id: 'emp-4', date: '2026-03-06', status: 'present', check_in: '08:00', check_out: '17:00', created_by: 'supervisor-1', created_at: '2026-03-06T08:00:00Z' },
  { id: 'att-41', employee_id: 'emp-4', date: '2026-03-07', status: 'present', check_in: '08:00', check_out: '17:00', created_by: 'supervisor-1', created_at: '2026-03-07T08:00:00Z' },
  { id: 'att-42', employee_id: 'emp-4', date: '2026-03-10', status: 'present', check_in: '08:00', check_out: '17:00', created_by: 'supervisor-1', created_at: '2026-03-10T08:00:00Z' },
  { id: 'att-43', employee_id: 'emp-4', date: '2026-03-11', status: 'present', check_in: '08:00', check_out: '17:00', created_by: 'supervisor-1', created_at: '2026-03-11T08:00:00Z' },
  { id: 'att-44', employee_id: 'emp-4', date: '2026-03-12', status: 'present', check_in: '08:00', check_out: '17:00', created_by: 'supervisor-1', created_at: '2026-03-12T08:00:00Z' },
];

export const payrolls: Payroll[] = [
  {
    id: 'pay-1',
    employee_id: 'emp-1',
    period_start: '2026-02-01',
    period_end: '2026-02-28',
    total_days_present: 24,
    base_salary: 2400000, // 24 * 100000
    bonus: 500000,
    deductions: 0,
    total_salary: 2900000,
    payment_status: 'paid',
    payment_date: '2026-03-01T10:00:00Z',
    notes: 'Gaji bulan Februari 2026',
    created_by: 'owner-1',
    created_at: '2026-03-01T10:00:00Z',
  },
  {
    id: 'pay-2',
    employee_id: 'emp-2',
    period_start: '2026-02-01',
    period_end: '2026-02-28',
    total_days_present: 26,
    base_salary: 2340000, // 26 * 90000
    bonus: 300000,
    deductions: 50000,
    total_salary: 2590000,
    payment_status: 'paid',
    payment_date: '2026-03-01T10:00:00Z',
    notes: 'Gaji bulan Februari 2026',
    created_by: 'owner-1',
    created_at: '2026-03-01T10:00:00Z',
  },
  {
    id: 'pay-3',
    employee_id: 'emp-3',
    period_start: '2026-02-01',
    period_end: '2026-02-28',
    total_days_present: 25,
    base_salary: 3000000, // 25 * 120000
    bonus: 400000,
    deductions: 0,
    total_salary: 3400000,
    payment_status: 'paid',
    payment_date: '2026-03-01T10:00:00Z',
    notes: 'Gaji bulan Februari 2026',
    created_by: 'owner-1',
    created_at: '2026-03-01T10:00:00Z',
  },
  {
    id: 'pay-4',
    employee_id: 'emp-4',
    period_start: '2026-02-01',
    period_end: '2026-02-28',
    total_days_present: 26,
    base_salary: 2470000, // 26 * 95000
    bonus: 350000,
    deductions: 0,
    total_salary: 2820000,
    payment_status: 'paid',
    payment_date: '2026-03-01T10:00:00Z',
    notes: 'Gaji bulan Februari 2026',
    created_by: 'owner-1',
    created_at: '2026-03-01T10:00:00Z',
  },
];

export const operationalItems: OperationalItem[] = [
  {
    id: 'opi-1',
    item_code: 'OPS-001',
    name: 'Kantong Kresek Kecil',
    category: 'packaging',
    unit: 'pack',
    current_stock: 45,
    min_stock: 20,
    unit_price: 15000,
    last_purchase_date: '2026-03-10T08:00:00Z',
    last_purchase_quantity: 50,
    supplier_name: 'CV Plastik Jaya',
    notes: '1 pack = 100 pcs',
    created_at: '2026-01-01T10:00:00Z',
    updated_at: '2026-03-10T08:00:00Z',
  },
  {
    id: 'opi-2',
    item_code: 'OPS-002',
    name: 'Kantong Kresek Besar',
    category: 'packaging',
    unit: 'pack',
    current_stock: 28,
    min_stock: 15,
    unit_price: 25000,
    last_purchase_date: '2026-03-10T08:00:00Z',
    last_purchase_quantity: 30,
    supplier_name: 'CV Plastik Jaya',
    notes: '1 pack = 100 pcs',
    created_at: '2026-01-01T10:00:00Z',
    updated_at: '2026-03-10T08:00:00Z',
  },
  {
    id: 'opi-3',
    item_code: 'OPS-003',
    name: 'Kertas Struk Thermal',
    category: 'stationery',
    unit: 'roll',
    current_stock: 15,
    min_stock: 10,
    unit_price: 8000,
    last_purchase_date: '2026-03-05T09:00:00Z',
    last_purchase_quantity: 20,
    supplier_name: 'Toko ATK Maju',
    notes: 'Roll 57mm x 30m',
    created_at: '2026-01-01T10:00:00Z',
    updated_at: '2026-03-05T09:00:00Z',
  },
  {
    id: 'opi-4',
    item_code: 'OPS-004',
    name: 'Tinta Printer Hitam',
    category: 'stationery',
    unit: 'bottle',
    current_stock: 3,
    min_stock: 2,
    unit_price: 45000,
    last_purchase_date: '2026-02-20T10:00:00Z',
    last_purchase_quantity: 5,
    supplier_name: 'Toko ATK Maju',
    notes: 'Tinta untuk printer Epson L3110',
    created_at: '2026-01-01T10:00:00Z',
    updated_at: '2026-02-20T10:00:00Z',
  },
  {
    id: 'opi-5',
    item_code: 'OPS-005',
    name: 'Tinta Printer Warna (Cyan)',
    category: 'stationery',
    unit: 'bottle',
    current_stock: 2,
    min_stock: 2,
    unit_price: 45000,
    last_purchase_date: '2026-02-20T10:00:00Z',
    last_purchase_quantity: 3,
    supplier_name: 'Toko ATK Maju',
    notes: 'Tinta untuk printer Epson L3110',
    created_at: '2026-01-01T10:00:00Z',
    updated_at: '2026-02-20T10:00:00Z',
  },
  {
    id: 'opi-6',
    item_code: 'OPS-006',
    name: 'Lakban Coklat',
    category: 'packaging',
    unit: 'roll',
    current_stock: 12,
    min_stock: 8,
    unit_price: 12000,
    last_purchase_date: '2026-03-01T11:00:00Z',
    last_purchase_quantity: 15,
    supplier_name: 'CV Plastik Jaya',
    notes: 'Lakban 48mm x 90m',
    created_at: '2026-01-01T10:00:00Z',
    updated_at: '2026-03-01T11:00:00Z',
  },
  {
    id: 'opi-7',
    item_code: 'OPS-007',
    name: 'Spidol Permanen Hitam',
    category: 'stationery',
    unit: 'pcs',
    current_stock: 8,
    min_stock: 5,
    unit_price: 5000,
    last_purchase_date: '2026-02-15T14:00:00Z',
    last_purchase_quantity: 12,
    supplier_name: 'Toko ATK Maju',
    notes: 'Snowman permanent marker',
    created_at: '2026-01-01T10:00:00Z',
    updated_at: '2026-02-15T14:00:00Z',
  },
  {
    id: 'opi-8',
    item_code: 'OPS-008',
    name: 'Sapu Lidi',
    category: 'cleaning',
    unit: 'pcs',
    current_stock: 5,
    min_stock: 3,
    unit_price: 15000,
    last_purchase_date: '2026-03-08T08:00:00Z',
    last_purchase_quantity: 6,
    supplier_name: 'Pasar Tradisional',
    notes: 'Sapu lidi untuk kebersihan toko',
    created_at: '2026-01-01T10:00:00Z',
    updated_at: '2026-03-08T08:00:00Z',
  },
  {
    id: 'opi-9',
    item_code: 'OPS-009',
    name: 'Pel Lantai + Gagang',
    category: 'cleaning',
    unit: 'set',
    current_stock: 2,
    min_stock: 1,
    unit_price: 35000,
    last_purchase_date: '2026-01-15T09:00:00Z',
    last_purchase_quantity: 2,
    supplier_name: 'Pasar Tradisional',
    notes: 'Set pel dengan gagang stainless',
    created_at: '2026-01-01T10:00:00Z',
    updated_at: '2026-01-15T09:00:00Z',
  },
  {
    id: 'opi-10',
    item_code: 'OPS-010',
    name: 'Karung Beras Bekas',
    category: 'packaging',
    unit: 'pcs',
    current_stock: 50,
    min_stock: 30,
    unit_price: 2000,
    last_purchase_date: '2026-03-12T10:00:00Z',
    last_purchase_quantity: 100,
    supplier_name: 'Pengepul Lokal',
    notes: 'Untuk packaging material curah',
    created_at: '2026-01-01T10:00:00Z',
    updated_at: '2026-03-12T10:00:00Z',
  },
  {
    id: 'opi-11',
    item_code: 'OPS-011',
    name: 'Tali Rafia',
    category: 'packaging',
    unit: 'roll',
    current_stock: 6,
    min_stock: 4,
    unit_price: 18000,
    last_purchase_date: '2026-02-28T13:00:00Z',
    last_purchase_quantity: 8,
    supplier_name: 'CV Plastik Jaya',
    notes: 'Roll 1kg untuk packaging',
    created_at: '2026-01-01T10:00:00Z',
    updated_at: '2026-02-28T13:00:00Z',
  },
  {
    id: 'opi-12',
    item_code: 'OPS-012',
    name: 'Pembersih Lantai (Wipol)',
    category: 'cleaning',
    unit: 'bottle',
    current_stock: 4,
    min_stock: 3,
    unit_price: 25000,
    last_purchase_date: '2026-03-01T08:00:00Z',
    last_purchase_quantity: 6,
    supplier_name: 'Grosir Bersih',
    notes: 'Wipol 800ml',
    created_at: '2026-01-01T10:00:00Z',
    updated_at: '2026-03-01T08:00:00Z',
  },
];

export const operationalExpenses: OperationalExpense[] = [
  {
    id: 'opx-1',
    item_id: 'opi-1',
    item_name: 'Kantong Kresek Kecil',
    transaction_type: 'purchase',
    quantity: 50,
    unit_price: 15000,
    total_amount: 750000,
    date: '2026-03-10T08:00:00Z',
    notes: 'Stok bulanan',
    created_by: 'operator-1',
    created_at: '2026-03-10T08:00:00Z',
  },
  {
    id: 'opx-2',
    item_id: 'opi-2',
    item_name: 'Kantong Kresek Besar',
    transaction_type: 'purchase',
    quantity: 30,
    unit_price: 25000,
    total_amount: 750000,
    date: '2026-03-10T08:00:00Z',
    notes: 'Stok bulanan',
    created_by: 'operator-1',
    created_at: '2026-03-10T08:00:00Z',
  },
  {
    id: 'opx-3',
    item_id: 'opi-1',
    item_name: 'Kantong Kresek Kecil',
    transaction_type: 'usage',
    quantity: 5,
    unit_price: 15000,
    total_amount: 75000,
    date: '2026-03-15T17:00:00Z',
    notes: 'Pemakaian minggu kedua Maret',
    created_by: 'operator-1',
    created_at: '2026-03-15T17:00:00Z',
  },
  {
    id: 'opx-4',
    item_id: 'opi-3',
    item_name: 'Kertas Struk Thermal',
    transaction_type: 'purchase',
    quantity: 20,
    unit_price: 8000,
    total_amount: 160000,
    date: '2026-03-05T09:00:00Z',
    notes: 'Stok struk',
    created_by: 'operator-1',
    created_at: '2026-03-05T09:00:00Z',
  },
  {
    id: 'opx-5',
    item_id: 'opi-3',
    item_name: 'Kertas Struk Thermal',
    transaction_type: 'usage',
    quantity: 5,
    unit_price: 8000,
    total_amount: 40000,
    date: '2026-03-12T18:00:00Z',
    notes: 'Ganti roll kasir 1 & 2',
    created_by: 'operator-1',
    created_at: '2026-03-12T18:00:00Z',
  },
  {
    id: 'opx-6',
    item_id: 'opi-12',
    item_name: 'Pembersih Lantai (Wipol)',
    transaction_type: 'purchase',
    quantity: 6,
    unit_price: 25000,
    total_amount: 150000,
    date: '2026-03-01T08:00:00Z',
    notes: 'Stok cleaning supplies',
    created_by: 'operator-1',
    created_at: '2026-03-01T08:00:00Z',
  },
];

export const customers: Customer[] = [
  {
    id: 'cust-1',
    customer_code: 'CUST-001',
    name: 'PT Maju Jaya Konstruksi',
    phone: '081234567890',
    email: 'admin@majujaya.co.id',
    address: 'Jl. Gatot Subroto No. 123, Jakarta Selatan',
    customer_type: 'project',
    credit_limit: 100000000, // 100 juta
    payment_terms_days: 60,
    total_purchases: 450000000,
    total_outstanding: 45000000,
    status: 'active',
    notes: 'Kontraktor proyek gedung',
    created_at: '2025-06-15T10:00:00Z',
  },
  {
    id: 'cust-2',
    customer_code: 'CUST-002',
    name: 'CV Sentosa Bangunan',
    phone: '081298765432',
    email: 'sentosa@gmail.com',
    address: 'Jl. Sudirman No. 45, Tangerang',
    customer_type: 'wholesale',
    credit_limit: 50000000, // 50 juta
    payment_terms_days: 30,
    total_purchases: 180000000,
    total_outstanding: 22500000,
    status: 'active',
    notes: 'Toko bangunan reseller',
    created_at: '2025-08-20T10:00:00Z',
  },
  {
    id: 'cust-3',
    customer_code: 'CUST-003',
    name: 'Toko Berkah Material',
    phone: '081355556666',
    email: 'berkah@yahoo.com',
    address: 'Jl. Ahmad Yani No. 78, Bekasi',
    customer_type: 'wholesale',
    credit_limit: 30000000, // 30 juta
    payment_terms_days: 30,
    total_purchases: 95000000,
    total_outstanding: 8500000,
    status: 'active',
    created_at: '2025-10-05T10:00:00Z',
  },
  {
    id: 'cust-4',
    customer_code: 'CUST-004',
    name: 'Bapak Suharto',
    phone: '081299998888',
    address: 'Jl. Mawar No. 12, Depok',
    customer_type: 'retail',
    credit_limit: 10000000, // 10 juta
    payment_terms_days: 14,
    total_purchases: 25000000,
    total_outstanding: 3500000,
    status: 'active',
    notes: 'Pelanggan retail tetap untuk renovasi rumah',
    created_at: '2026-01-10T10:00:00Z',
  },
  {
    id: 'cust-5',
    customer_code: 'CUST-005',
    name: 'PT Karya Abadi',
    phone: '081377779999',
    email: 'karya.abadi@gmail.com',
    address: 'Jl. Raya Bogor KM 25, Cibinong',
    customer_type: 'project',
    credit_limit: 80000000, // 80 juta
    payment_terms_days: 45,
    total_purchases: 320000000,
    total_outstanding: 0, // Lunas semua
    status: 'active',
    notes: 'Developer perumahan',
    created_at: '2025-07-01T10:00:00Z',
  },
  {
    id: 'cust-6',
    customer_code: 'CUST-006',
    name: 'Ibu Dewi',
    phone: '081266667777',
    address: 'Jl. Melati No. 34, Jakarta Timur',
    customer_type: 'retail',
    credit_limit: 5000000, // 5 juta
    payment_terms_days: 14,
    total_purchases: 12000000,
    total_outstanding: 2200000,
    status: 'active',
    notes: 'Pelanggan retail',
    created_at: '2026-02-12T10:00:00Z',
  },
];

export const customerTransactions: CustomerTransaction[] = [
  // PT Maju Jaya Konstruksi - Multiple transactions
  {
    id: 'ctx-1',
    transaction_number: 'INV-2026-001',
    customer_id: 'cust-1',
    transaction_date: '2026-01-15T10:00:00Z',
    due_date: '2026-03-16T23:59:59Z', // 60 days payment terms
    items: [
      { product_id: 'prod-1', quantity: 100, unit_price: 75000, total: 7500000, cost_price: 62000 },
      { product_id: 'prod-7', quantity: 20, unit_price: 850000, total: 17000000, cost_price: 680000 },
    ],
    subtotal: 24500000,
    tax_amount: 2450000,
    total_amount: 26950000,
    cost_amount: 19800000,
    payment_status: 'paid',
    payments: [
      {
        id: 'pay-1',
        payment_date: '2026-03-10T14:00:00Z',
        payment_method: 'mixed',
        cash_amount: 10000000,
        transfer_amount: 16950000,
        total_amount: 26950000,
        bank_name: 'BCA',
        reference_number: 'TRF-20260310-001',
        notes: 'Pembayaran sebagian cash, sebagian transfer',
        received_by: 'operator-1',
        created_at: '2026-03-10T14:00:00Z',
      },
    ],
    total_paid: 26950000,
    remaining_balance: 0,
    notes: 'Pesanan proyek gedung fase 1',
    created_by: 'operator-1',
    created_at: '2026-01-15T10:00:00Z',
    updated_at: '2026-03-10T14:00:00Z',
  },
  {
    id: 'ctx-2',
    transaction_number: 'INV-2026-002',
    customer_id: 'cust-1',
    transaction_date: '2026-02-20T10:00:00Z',
    due_date: '2026-04-21T23:59:59Z',
    items: [
      { product_id: 'prod-1', quantity: 150, unit_price: 75000, total: 11250000, cost_price: 62000 },
      { product_id: 'prod-3', quantity: 200, unit_price: 15000, total: 3000000, cost_price: 12000 },
      { product_id: 'prod-8', quantity: 50, unit_price: 125000, total: 6250000, cost_price: 95000 },
    ],
    subtotal: 20500000,
    tax_amount: 2050000,
    total_amount: 22550000,
    cost_amount: 16900000,
    payment_status: 'partial',
    payments: [
      {
        id: 'pay-2',
        payment_date: '2026-03-05T11:00:00Z',
        payment_method: 'transfer',
        cash_amount: 0,
        transfer_amount: 10000000,
        total_amount: 10000000,
        bank_name: 'Mandiri',
        reference_number: 'TRF-20260305-002',
        notes: 'Pembayaran pertama',
        received_by: 'supervisor-1',
        created_at: '2026-03-05T11:00:00Z',
      },
    ],
    total_paid: 10000000,
    remaining_balance: 12550000,
    notes: 'Proyek gedung fase 2',
    created_by: 'operator-1',
    created_at: '2026-02-20T10:00:00Z',
    updated_at: '2026-03-05T11:00:00Z',
  },
  {
    id: 'ctx-3',
    transaction_number: 'INV-2026-003',
    customer_id: 'cust-1',
    transaction_date: '2026-03-10T10:00:00Z',
    due_date: '2026-05-09T23:59:59Z',
    items: [
      { product_id: 'prod-2', quantity: 300, unit_price: 18000, total: 5400000, cost_price: 14500 },
      { product_id: 'prod-9', quantity: 100, unit_price: 65000, total: 6500000, cost_price: 52000 },
    ],
    subtotal: 11900000,
    tax_amount: 1190000,
    total_amount: 13090000,
    cost_amount: 9550000,
    payment_status: 'unpaid',
    payments: [],
    total_paid: 0,
    remaining_balance: 13090000,
    notes: 'Proyek gedung fase 3',
    created_by: 'operator-1',
    created_at: '2026-03-10T10:00:00Z',
    updated_at: '2026-03-10T10:00:00Z',
  },
  
  // CV Sentosa Bangunan
  {
    id: 'ctx-4',
    transaction_number: 'INV-2026-004',
    customer_id: 'cust-2',
    transaction_date: '2026-02-15T10:00:00Z',
    due_date: '2026-03-17T23:59:59Z', // 30 days
    items: [
      { product_id: 'prod-4', quantity: 50, unit_price: 450000, total: 22500000, cost_price: 360000 },
    ],
    subtotal: 22500000,
    tax_amount: 2250000,
    total_amount: 24750000,
    cost_amount: 18000000,
    payment_status: 'unpaid',
    payments: [],
    total_paid: 0,
    remaining_balance: 24750000,
    notes: 'Pesanan cat untuk toko',
    created_by: 'operator-1',
    created_at: '2026-02-15T10:00:00Z',
    updated_at: '2026-02-15T10:00:00Z',
  },
  
  // Toko Berkah Material
  {
    id: 'ctx-5',
    transaction_number: 'INV-2026-005',
    customer_id: 'cust-3',
    transaction_date: '2026-02-28T10:00:00Z',
    due_date: '2026-03-30T23:59:59Z',
    items: [
      { product_id: 'prod-5', quantity: 30, unit_price: 285000, total: 8550000, cost_price: 228000 },
    ],
    subtotal: 8550000,
    tax_amount: 855000,
    total_amount: 9405000,
    cost_amount: 6840000,
    payment_status: 'partial',
    payments: [
      {
        id: 'pay-3',
        payment_date: '2026-03-15T09:30:00Z',
        payment_method: 'cash',
        cash_amount: 5000000,
        transfer_amount: 0,
        total_amount: 5000000,
        notes: 'Pembayaran tunai pertama',
        received_by: 'operator-1',
        created_at: '2026-03-15T09:30:00Z',
      },
    ],
    total_paid: 5000000,
    remaining_balance: 4405000,
    notes: 'Pesanan PVC pipe',
    created_by: 'operator-1',
    created_at: '2026-02-28T10:00:00Z',
    updated_at: '2026-03-15T09:30:00Z',
  },
  
  // Bapak Suharto
  {
    id: 'ctx-6',
    transaction_number: 'INV-2026-006',
    customer_id: 'cust-4',
    transaction_date: '2026-03-05T10:00:00Z',
    due_date: '2026-03-19T23:59:59Z', // 14 days
    items: [
      { product_id: 'prod-1', quantity: 20, unit_price: 75000, total: 1500000, cost_price: 62000 },
      { product_id: 'prod-3', quantity: 50, unit_price: 15000, total: 750000, cost_price: 12000 },
    ],
    subtotal: 2250000,
    tax_amount: 225000,
    total_amount: 2475000,
    cost_amount: 1840000,
    payment_status: 'unpaid',
    payments: [],
    total_paid: 0,
    remaining_balance: 2475000,
    notes: 'Renovasi rumah',
    created_by: 'operator-1',
    created_at: '2026-03-05T10:00:00Z',
    updated_at: '2026-03-05T10:00:00Z',
  },
  {
    id: 'ctx-7',
    transaction_number: 'INV-2026-007',
    customer_id: 'cust-4',
    transaction_date: '2026-01-20T10:00:00Z',
    due_date: '2026-02-03T23:59:59Z',
    items: [
      { product_id: 'prod-4', quantity: 5, unit_price: 450000, total: 2250000, cost_price: 360000 },
    ],
    subtotal: 2250000,
    tax_amount: 225000,
    total_amount: 2475000,
    cost_amount: 1800000,
    payment_status: 'paid',
    payments: [
      {
        id: 'pay-4',
        payment_date: '2026-02-01T14:00:00Z',
        payment_method: 'mixed',
        cash_amount: 1000000,
        transfer_amount: 1475000,
        total_amount: 2475000,
        bank_name: 'BRI',
        reference_number: 'TRF-20260201-003',
        notes: 'Cash 1jt, transfer sisanya',
        received_by: 'operator-1',
        created_at: '2026-02-01T14:00:00Z',
      },
    ],
    total_paid: 2475000,
    remaining_balance: 0,
    notes: 'Pembelian cat rumah',
    created_by: 'operator-1',
    created_at: '2026-01-20T10:00:00Z',
    updated_at: '2026-02-01T14:00:00Z',
  },
  
  // Ibu Dewi
  {
    id: 'ctx-8',
    transaction_number: 'INV-2026-008',
    customer_id: 'cust-6',
    transaction_date: '2026-03-12T10:00:00Z',
    due_date: '2026-03-26T23:59:59Z', // 14 days
    items: [
      { product_id: 'prod-8', quantity: 10, unit_price: 125000, total: 1250000, cost_price: 95000 },
      { product_id: 'prod-9', quantity: 15, unit_price: 65000, total: 975000, cost_price: 52000 },
    ],
    subtotal: 2225000,
    tax_amount: 222500,
    total_amount: 2447500,
    cost_amount: 1730000,
    payment_status: 'partial',
    payments: [
      {
        id: 'pay-5',
        payment_date: '2026-03-14T10:00:00Z',
        payment_method: 'cash',
        cash_amount: 1500000,
        transfer_amount: 0,
        total_amount: 1500000,
        notes: 'Bayar sebagian dulu',
        received_by: 'operator-1',
        created_at: '2026-03-14T10:00:00Z',
      },
    ],
    total_paid: 1500000,
    remaining_balance: 947500,
    notes: 'Renovasi kamar mandi',
    created_by: 'operator-1',
    created_at: '2026-03-12T10:00:00Z',
    updated_at: '2026-03-14T10:00:00Z',
  },
  
  // PT Karya Abadi - All paid
  {
    id: 'ctx-9',
    transaction_number: 'INV-2026-009',
    customer_id: 'cust-5',
    transaction_date: '2026-02-01T10:00:00Z',
    due_date: '2026-03-18T23:59:59Z', // 45 days
    items: [
      { product_id: 'prod-1', quantity: 200, unit_price: 75000, total: 15000000, cost_price: 62000 },
      { product_id: 'prod-2', quantity: 500, unit_price: 18000, total: 9000000, cost_price: 14500 },
    ],
    subtotal: 24000000,
    tax_amount: 2400000,
    total_amount: 26400000,
    cost_amount: 19650000,
    payment_status: 'paid',
    payments: [
      {
        id: 'pay-6',
        payment_date: '2026-03-15T16:00:00Z',
        payment_method: 'transfer',
        cash_amount: 0,
        transfer_amount: 26400000,
        total_amount: 26400000,
        bank_name: 'BCA',
        reference_number: 'TRF-20260315-004',
        notes: 'Lunas via transfer',
        received_by: 'supervisor-1',
        created_at: '2026-03-15T16:00:00Z',
      },
    ],
    total_paid: 26400000,
    remaining_balance: 0,
    notes: 'Proyek perumahan Cibinong',
    created_by: 'operator-1',
    created_at: '2026-02-01T10:00:00Z',
    updated_at: '2026-03-15T16:00:00Z',
  },
];