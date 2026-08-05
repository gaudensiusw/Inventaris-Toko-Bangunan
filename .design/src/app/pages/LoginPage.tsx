import React, { useState } from 'react';
import { useAuth } from '../contexts/AuthContext';
import { Button } from '../components/ui/button';
import { Input } from '../components/ui/input';
import { Label } from '../components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../components/ui/card';
import { Building2, Lock, Mail } from 'lucide-react';
import { toast } from 'sonner';

export const LoginPage: React.FC = () => {
  const { login } = useAuth();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');

  const handleLogin = (e: React.FormEvent) => {
    e.preventDefault();
    
    const success = login(email, password);
    
    if (success) {
      toast.success('Login successful!');
    } else {
      toast.error('Invalid credentials. Try the demo accounts below.');
    }
  };

  const quickLogin = (role: 'operator' | 'supervisor' | 'owner') => {
    const accounts = {
      operator: 'operator@tokobangunan.com',
      supervisor: 'supervisor@tokobangunan.com',
      owner: 'owner@tokobangunan.com',
    };
    
    setEmail(accounts[role]);
    setPassword('demo123');
    
    // Auto-login
    setTimeout(() => {
      login(accounts[role], 'demo123');
      toast.success(`Logged in as ${role}`);
    }, 100);
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 p-4">
      <div className="w-full max-w-md">
        {/* Logo and Title */}
        <div className="text-center mb-8">
          <div className="flex justify-center mb-4">
            <div className="w-16 h-16 bg-blue-600 rounded-2xl flex items-center justify-center">
              <Building2 className="w-10 h-10 text-white" />
            </div>
          </div>
          <h1 className="text-3xl font-bold text-white mb-2">
            Toko Bangunan IMS
          </h1>
          <p className="text-slate-400">
            Inventory Management System
          </p>
        </div>

        {/* Login Card */}
        <Card>
          <CardHeader>
            <CardTitle>Sign In</CardTitle>
            <CardDescription>
              Enter your credentials to access the system
            </CardDescription>
          </CardHeader>
          <CardContent>
            <form onSubmit={handleLogin} className="space-y-4">
              <div className="space-y-2">
                <Label htmlFor="email">Email</Label>
                <div className="relative">
                  <Mail className="absolute left-3 top-3 h-4 w-4 text-slate-400" />
                  <Input
                    id="email"
                    type="email"
                    placeholder="user@tokobangunan.com"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    className="pl-10"
                    required
                  />
                </div>
              </div>

              <div className="space-y-2">
                <Label htmlFor="password">Password</Label>
                <div className="relative">
                  <Lock className="absolute left-3 top-3 h-4 w-4 text-slate-400" />
                  <Input
                    id="password"
                    type="password"
                    placeholder="••••••••"
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                    className="pl-10"
                    required
                  />
                </div>
              </div>

              <Button type="submit" className="w-full">
                Sign In
              </Button>
            </form>

            {/* Quick Login Options */}
            <div className="mt-6">
              <div className="relative">
                <div className="absolute inset-0 flex items-center">
                  <span className="w-full border-t" />
                </div>
                <div className="relative flex justify-center text-xs uppercase">
                  <span className="bg-white px-2 text-slate-500">
                    Quick Login (Demo)
                  </span>
                </div>
              </div>

              <div className="grid grid-cols-3 gap-2 mt-4">
                <Button
                  type="button"
                  variant="outline"
                  size="sm"
                  onClick={() => quickLogin('operator')}
                >
                  Operator
                </Button>
                <Button
                  type="button"
                  variant="outline"
                  size="sm"
                  onClick={() => quickLogin('supervisor')}
                >
                  Supervisor
                </Button>
                <Button
                  type="button"
                  variant="outline"
                  size="sm"
                  onClick={() => quickLogin('owner')}
                >
                  Owner
                </Button>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Demo Info */}
        <Card className="mt-4 bg-blue-50 border-blue-200">
          <CardContent className="pt-6">
            <p className="text-sm text-blue-900">
              <strong>Demo Accounts:</strong>
            </p>
            <ul className="text-xs text-blue-800 mt-2 space-y-1">
              <li>• <strong>Operator:</strong> operator@tokobangunan.com</li>
              <li>• <strong>Supervisor:</strong> supervisor@tokobangunan.com</li>
              <li>• <strong>Owner:</strong> owner@tokobangunan.com</li>
              <li className="mt-2">Password: demo123 (any password works)</li>
            </ul>
          </CardContent>
        </Card>
      </div>
    </div>
  );
};
