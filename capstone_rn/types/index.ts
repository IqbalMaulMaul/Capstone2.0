// ─── Auth ────────────────────────────────────────────
export interface User {
  id: number;
  name: string;
  email: string;
  role: 'owner' | 'admin' | 'kitchen' | 'finance';
  avatar: string | null;
}

export interface LoginResponse {
  status: string;
  message: string;
  data: {
    token: string;
    user: User;
  };
}

// ─── Categories ──────────────────────────────────────
export interface Category {
  id: number;
  name: string;
  slug: string;
  icon: string | null;
  sort_order: number;
  is_active: boolean;
  menus_count: number;
}

export interface CategoryFormData {
  name: string;
  icon?: string;
  sort_order?: number;
}

// ─── Menus ───────────────────────────────────────────
export interface Menu {
  id: number;
  name: string;
  slug: string;
  description: string | null;
  category_id: number;
  category_name: string;
  price: number;
  formatted_price: string;
  is_available: boolean;
  image_url: string | null;
  sort_order: number;
}

export interface MenuFormData {
  name: string;
  category_id: number;
  description?: string;
  price: number;
  is_available?: boolean;
  image?: any; // File from image picker
}

// ─── Rooms ───────────────────────────────────────────
export interface Room {
  id: number;
  room_number: string;
  floor: number;
  is_active: boolean;
  qr_token: string;
  qr_url: string;
}

export interface RoomFormData {
  room_number: string;
  floor: number;
  is_active?: boolean;
  regenerate_token?: boolean;
}

// ─── Orders ──────────────────────────────────────────
export interface OrderListItem {
  id: number;
  order_number: string;
  room_number: string;
  guest_name: string | null;
  status: string;
  status_label: string;
  total: number;
  formatted_total: string;
  payment_status: string | null;
  payment_method: string | null;
  created_at: string;
  created_at_human: string;
}

export interface OrderItem {
  id: number;
  menu_name: string;
  menu_price: number;
  quantity: number;
  subtotal: number;
  notes: string | null;
  image_url: string | null;
}

export interface PaymentInfo {
  id: number;
  transaction_id: string | null;
  amount: number;
  formatted_amount: string;
  status: string;
  method: string | null;
  payment_type: string | null;
  paid_at: string | null;
  paid_at_human: string | null;
}

export interface OrderDetail {
  id: number;
  order_number: string;
  room_number: string;
  guest_name: string | null;
  status: string;
  status_label: string;
  notes: string | null;
  subtotal: number;
  tax: number;
  total: number;
  formatted_total: string;
  estimated_delivery: string | null;
  created_at: string;
  created_at_human: string;
  items: OrderItem[];
  payment: PaymentInfo | null;
}

// ─── Kitchen ─────────────────────────────────────────
export interface KitchenOrderItem {
  id: number;
  menu_name: string;
  quantity: number;
  notes: string | null;
}

export interface KitchenOrder {
  id: number;
  order_number: string;
  room_number: string;
  guest_name: string | null;
  status: string;
  status_label: string;
  notes: string | null;
  created_at: string;
  created_at_human: string;
  items: KitchenOrderItem[];
}

export interface KitchenMenu {
  id: number;
  name: string;
  is_available: boolean;
}

export interface KitchenCategory {
  id: number;
  name: string;
  menus: KitchenMenu[];
}

export interface KitchenData {
  orders: KitchenOrder[];
  categories: KitchenCategory[];
}

// ─── Finance ─────────────────────────────────────────
export interface TopMenu {
  menu_id: number;
  menu_name: string;
  total_qty: number;
  total_revenue: number;
  formatted_revenue: string;
}

export interface RecentPayment {
  id: number;
  order_number: string;
  room_number: string;
  amount: number;
  formatted_amount: string;
  payment_type: string | null;
  paid_at: string | null;
  paid_at_human: string;
}

export interface FinanceDashboard {
  todayRevenue: number;
  weekRevenue: number;
  monthRevenue: number;
  totalTransactions: number;
  pendingTransactions: number;
  failedTransactions: number;
  averageTransaction: number;
  chart: {
    labels: string[];
    data: number[];
  };
  topMenus: TopMenu[];
  recentPayments: RecentPayment[];
}

// ─── Dashboard ───────────────────────────────────────
export interface DashboardData {
  todayOrders: number;
  pendingOrders: number;
  kitchenActive: number;
  todayRevenue: number;
  recentOrders: OrderListItem[];
}

// ─── Payments ────────────────────────────────────────
export interface PaymentListItem {
  id: number;
  order_id: number;
  order_number: string;
  room_number: string;
  transaction_id: string | null;
  amount: number;
  formatted_amount: string;
  status: string;
  method: string | null;
  payment_type: string | null;
  paid_at: string | null;
  paid_at_human: string | null;
  created_at: string;
}

// ─── API Response Wrappers ───────────────────────────
export interface ApiResponse<T> {
  status: string;
  message?: string;
  data: T;
}

export interface PaginatedResponse<T> {
  status: string;
  data: {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}
