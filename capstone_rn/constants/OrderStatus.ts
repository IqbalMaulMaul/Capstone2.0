import { Colors } from './Colors';

export const ORDER_STATUS = {
  PENDING_PAYMENT: 'pending_payment',
  PAID: 'paid',
  ACCEPTED: 'accepted',
  PROCESSING: 'processing',
  READY: 'ready',
  DELIVERED: 'delivered',
  COMPLETED: 'completed',
  CANCELLED: 'cancelled',
} as const;

export type OrderStatusType = typeof ORDER_STATUS[keyof typeof ORDER_STATUS];

export const ORDER_STATUS_LABELS: Record<OrderStatusType, string> = {
  pending_payment: 'Menunggu Pembayaran',
  paid: 'Sudah Dibayar',
  accepted: 'Diterima Kitchen',
  processing: 'Sedang Diproses',
  ready: 'Siap Diantar',
  delivered: 'Sedang Diantar',
  completed: 'Selesai',
  cancelled: 'Dibatalkan',
};

export const ORDER_STATUS_COLORS: Record<OrderStatusType, { bg: string; text: string; border: string }> = {
  pending_payment: { bg: Colors.warningBg, text: Colors.warning, border: Colors.warning },
  paid: { bg: Colors.infoBg, text: Colors.info, border: Colors.info },
  accepted: { bg: 'rgba(99, 102, 241, 0.15)', text: Colors.primary, border: Colors.primary },
  processing: { bg: 'rgba(6, 182, 212, 0.15)', text: Colors.secondary, border: Colors.secondary },
  ready: { bg: Colors.successBg, text: Colors.success, border: Colors.success },
  delivered: { bg: 'rgba(139, 92, 246, 0.15)', text: '#8B5CF6', border: '#8B5CF6' },
  completed: { bg: Colors.successBg, text: Colors.success, border: Colors.success },
  cancelled: { bg: Colors.errorBg, text: Colors.error, border: Colors.error },
};

export const ORDER_STATUS_ICONS: Record<OrderStatusType, string> = {
  pending_payment: 'hourglass-empty',
  paid: 'payment',
  accepted: 'thumb-up',
  processing: 'restaurant',
  ready: 'check-circle',
  delivered: 'delivery-dining',
  completed: 'verified',
  cancelled: 'cancel',
};

// Kitchen can transition to these statuses
export const KITCHEN_STATUS_TRANSITIONS: Record<string, OrderStatusType> = {
  paid: 'accepted',
  accepted: 'processing',
  processing: 'ready',
  ready: 'delivered',
};

export const KITCHEN_STATUS_ACTIONS: Record<string, string> = {
  paid: 'Terima Pesanan',
  accepted: 'Mulai Masak',
  processing: 'Siap Diantar',
  ready: 'Antar Sekarang',
};
