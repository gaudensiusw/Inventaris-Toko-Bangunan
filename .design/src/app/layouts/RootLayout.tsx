import { Outlet } from 'react-router';
import { AuthProvider } from '../contexts/AuthContext';
import { InventoryProvider } from '../contexts/InventoryContext';
import { Toaster } from '../components/ui/sonner';

export function RootLayout() {
  return (
    <AuthProvider>
      <InventoryProvider>
        <div className="min-h-screen">
          <Outlet />
          <Toaster position="top-right" />
        </div>
      </InventoryProvider>
    </AuthProvider>
  );
}