import api from './api';
import {
  ApiResponse,
  PaginatedResponse,
  DashboardData,
  Category,
  CategoryFormData,
  Menu,
  MenuFormData,
  Room,
  RoomFormData,
  OrderListItem,
  OrderDetail,
  PaymentListItem,
} from '../types';

export const adminService = {
  // ─── Dashboard ─────────────────────────────────────
  async getDashboard(): Promise<DashboardData> {
    const res = await api.get<ApiResponse<DashboardData>>('/admin/dashboard');
    return res.data.data;
  },

  // ─── Categories ────────────────────────────────────
  async getCategories(): Promise<Category[]> {
    const res = await api.get<ApiResponse<Category[]>>('/admin/categories');
    return res.data.data;
  },

  async createCategory(data: CategoryFormData): Promise<Category> {
    const res = await api.post<ApiResponse<Category>>('/admin/categories', data);
    return res.data.data;
  },

  async updateCategory(id: number, data: CategoryFormData): Promise<Category> {
    const res = await api.put<ApiResponse<Category>>(`/admin/categories/${id}`, data);
    return res.data.data;
  },

  async deleteCategory(id: number): Promise<void> {
    await api.delete(`/admin/categories/${id}`);
  },

  // ─── Menus ─────────────────────────────────────────
  async getMenus(): Promise<Menu[]> {
    const res = await api.get<ApiResponse<Menu[]>>('/admin/menus');
    return res.data.data;
  },

  async createMenu(data: MenuFormData): Promise<Menu> {
    const formData = new FormData();
    formData.append('name', data.name);
    formData.append('category_id', data.category_id.toString());
    formData.append('price', data.price.toString());
    if (data.description) formData.append('description', data.description);
    formData.append('is_available', data.is_available ? '1' : '0');

    if (data.image) {
      formData.append('image', {
        uri: data.image.uri,
        name: data.image.fileName || 'photo.jpg',
        type: data.image.mimeType || 'image/jpeg',
      } as any);
    }

    const res = await api.post<ApiResponse<Menu>>('/admin/menus', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    return res.data.data;
  },

  async updateMenu(id: number, data: MenuFormData): Promise<Menu> {
    const formData = new FormData();
    formData.append('name', data.name);
    formData.append('category_id', data.category_id.toString());
    formData.append('price', data.price.toString());
    if (data.description) formData.append('description', data.description);
    formData.append('is_available', data.is_available ? '1' : '0');

    if (data.image) {
      formData.append('image', {
        uri: data.image.uri,
        name: data.image.fileName || 'photo.jpg',
        type: data.image.mimeType || 'image/jpeg',
      } as any);
    }

    // POST (not PUT) because of file upload
    const res = await api.post<ApiResponse<Menu>>(`/admin/menus/${id}`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    return res.data.data;
  },

  async deleteMenu(id: number): Promise<void> {
    await api.delete(`/admin/menus/${id}`);
  },

  async toggleMenu(id: number): Promise<{ id: number; name: string; is_available: boolean }> {
    const res = await api.patch(`/admin/menus/${id}/toggle`);
    return res.data.data;
  },

  // ─── Rooms ─────────────────────────────────────────
  async getRooms(): Promise<Room[]> {
    const res = await api.get<ApiResponse<Room[]>>('/admin/rooms');
    return res.data.data;
  },

  async createRoom(data: RoomFormData): Promise<Room> {
    const res = await api.post<ApiResponse<Room>>('/admin/rooms', data);
    return res.data.data;
  },

  async updateRoom(id: number, data: RoomFormData): Promise<Room> {
    const res = await api.put<ApiResponse<Room>>(`/admin/rooms/${id}`, data);
    return res.data.data;
  },

  async deleteRoom(id: number): Promise<void> {
    await api.delete(`/admin/rooms/${id}`);
  },

  // ─── Orders ────────────────────────────────────────
  async getOrders(page = 1, status?: string): Promise<PaginatedResponse<OrderListItem>['data']> {
    const params: any = { page };
    if (status && status !== 'all') params.status = status;
    const res = await api.get('/admin/orders', { params });
    return res.data.data;
  },

  async getOrder(id: number): Promise<OrderDetail> {
    const res = await api.get<ApiResponse<OrderDetail>>(`/admin/orders/${id}`);
    return res.data.data;
  },

  // ─── Payments ──────────────────────────────────────
  async getPayments(page = 1, status?: string): Promise<PaginatedResponse<PaymentListItem>['data']> {
    const params: any = { page };
    if (status && status !== 'all') params.status = status;
    const res = await api.get('/admin/payments', { params });
    return res.data.data;
  },
};
