import api from './api';
import { ApiResponse, FinanceDashboard } from '../types';

export const financeService = {
  async getDashboard(): Promise<FinanceDashboard> {
    const res = await api.get<ApiResponse<FinanceDashboard>>('/finance/dashboard');
    return res.data.data;
  },
};
