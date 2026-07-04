import api from './api';
import { ApiResponse, KitchenData } from '../types';

export const kitchenService = {
  async getOrders(): Promise<KitchenData> {
    const res = await api.get<ApiResponse<KitchenData>>('/kitchen/orders');
    return res.data.data;
  },

  async updateOrderStatus(orderId: number, status: string): Promise<{ id: number; status: string; status_label: string }> {
    const res = await api.patch(`/kitchen/orders/${orderId}/status`, { status });
    return res.data.data;
  },

  async toggleMenu(menuId: number): Promise<{ id: number; name: string; is_available: boolean }> {
    const res = await api.patch(`/kitchen/menus/${menuId}/toggle`);
    return res.data.data;
  },
};
