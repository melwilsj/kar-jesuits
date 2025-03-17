import axios from "axios";

export class AppError extends Error {
  constructor(
    message: string,
    public code: string,
    public httpStatus?: number
  ) {
    super(message);
    this.name = 'AppError';
  }
}

export const handleApiError = (error: any): AppError => {
  if (error instanceof AppError) return error;
  
  if (axios.isAxiosError(error)) {
    const status = error.response?.status;
    const message = error.response?.data?.message || error.message;
    
    switch (status) {
      case 401:
        return new AppError('Session expired. Please login again.', 'AUTH_ERROR', status);
      case 403:
        return new AppError('You don\'t have permission to perform this action.', 'PERMISSION_ERROR', status);
      // Add other cases
    }
  }
  
  return new AppError('An unexpected error occurred.', 'UNKNOWN_ERROR');
};
