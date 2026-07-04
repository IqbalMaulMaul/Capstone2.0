export const Config = {
  // Production URL (Vercel deployment)
  API_BASE_URL: 'https://capstone2-0-eight.vercel.app/api',

  // Local development (Android Emulator)
  // API_BASE_URL: 'http://10.0.2.2:8000/api',

  // Local development (Physical device — adjust IP)
  // API_BASE_URL: 'http://192.168.1.x:8000/api',

  // App info
  APP_NAME: 'Hotel Room Service',
  APP_VERSION: '1.0.0',

  // Polling intervals (ms)
  KITCHEN_POLL_INTERVAL: 10000, // 10 seconds
  DASHBOARD_POLL_INTERVAL: 30000, // 30 seconds

  // Pagination
  DEFAULT_PAGE_SIZE: 20,
};
