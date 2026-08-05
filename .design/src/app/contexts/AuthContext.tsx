import React, { createContext, useContext, useState, ReactNode } from 'react';
import { User, users } from '../data/mockData';

interface AuthContextType {
  user: User | null;
  login: (email: string, password: string) => boolean;
  logout: () => void;
  hasPermission: (permission: string) => boolean;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};

interface AuthProviderProps {
  children: ReactNode;
}

export const AuthProvider: React.FC<AuthProviderProps> = ({ children }) => {
  const [user, setUser] = useState<User | null>(() => {
    // Check if user is stored in localStorage
    const storedUser = localStorage.getItem('currentUser');
    return storedUser ? JSON.parse(storedUser) : null;
  });

  const login = (email: string, password: string): boolean => {
    // Simple authentication (in real app, this would be API call)
    const foundUser = users.find(u => u.email === email);
    
    if (foundUser) {
      setUser(foundUser);
      localStorage.setItem('currentUser', JSON.stringify(foundUser));
      return true;
    }
    return false;
  };

  const logout = () => {
    setUser(null);
    localStorage.removeItem('currentUser');
  };

  const hasPermission = (permission: string): boolean => {
    if (!user) return false;

    const permissions: Record<string, string[]> = {
      operator: [
        'pos.access',
        'stock.in',
        'stock.out',
        'stock.view',
        'products.view',
      ],
      supervisor: [
        'pos.access',
        'stock.in',
        'stock.out',
        'stock.view',
        'stock.adjust',
        'products.view',
        'products.create',
        'products.edit',
        'suppliers.manage',
        'notifications.manage',
        'reports.basic',
      ],
      owner: [
        'pos.access',
        'stock.in',
        'stock.out',
        'stock.view',
        'stock.adjust',
        'products.view',
        'products.create',
        'products.edit',
        'products.delete',
        'suppliers.manage',
        'notifications.manage',
        'reports.basic',
        'reports.financial',
        'pricing.manage',
        'ml.predictions',
        'audit.view',
      ],
    };

    return permissions[user.role]?.includes(permission) || false;
  };

  return (
    <AuthContext.Provider value={{ user, login, logout, hasPermission }}>
      {children}
    </AuthContext.Provider>
  );
};