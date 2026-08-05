import React, { useState, useMemo } from 'react';
import { useInventory } from '../contexts/InventoryContext';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../components/ui/card';
import { Button } from '../components/ui/button';
import { Badge } from '../components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../components/ui/table';
import { Progress } from '../components/ui/progress';
import { Brain, TrendingUp, Calendar, Activity, AlertCircle, Sparkles } from 'lucide-react';
import { mlPredictions } from '../data/mockData';
import { LineChart, Line, BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';

export const MLPredictionsPage: React.FC = () => {
  const { products } = useInventory();
  
  // Group predictions by product
  const productPredictions = products.map(product => {
    const demandPrediction = mlPredictions.find(
      p => p.product_id === product.id && p.prediction_type === 'demand'
    );
    const durationPrediction = mlPredictions.find(
      p => p.product_id === product.id && p.prediction_type === 'stock_duration'
    );
    
    return {
      product,
      demand: demandPrediction,
      duration: durationPrediction,
    };
  }).filter(p => p.demand || p.duration);

  // Calculate reorder recommendations
  const reorderRecommendations = productPredictions
    .filter(p => p.duration && p.duration.predicted_value < 30)
    .sort((a, b) => (a.duration?.predicted_value || 999) - (b.duration?.predicted_value || 999));

  // Forecast trend (next 7 days) - memoized to prevent duplicate keys
  const forecastTrend = useMemo(() => {
    return Array.from({ length: 7 }, (_, i) => {
      const date = new Date();
      date.setDate(date.getDate() + i);
      const day = date.toLocaleDateString('id-ID', { weekday: 'short' });
      
      // Use deterministic calculations based on index instead of Math.random()
      return {
        day,
        cement: 45 + (i % 3) * 3,
        steel: 12 + (i % 4) * 1.5,
        bricks: 600 + (i % 5) * 50,
      };
    });
  }, []);

  const getConfidenceBadge = (confidence: number) => {
    if (confidence >= 0.8) return { variant: 'default' as any, label: 'High' };
    if (confidence >= 0.6) return { variant: 'secondary' as any, label: 'Medium' };
    return { variant: 'destructive' as any, label: 'Low' };
  };

  const getUrgencyBadge = (days: number) => {
    if (days < 7) return { variant: 'destructive' as any, label: 'Urgent', color: 'text-red-600' };
    if (days < 14) return { variant: 'default' as any, label: 'Soon', color: 'text-orange-600' };
    return { variant: 'secondary' as any, label: 'Normal', color: 'text-green-600' };
  };

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h2 className="text-2xl font-bold text-slate-900">ML Predictions</h2>
          <p className="text-slate-600">AI-powered demand forecasting and stock analytics</p>
        </div>
        <Badge className="w-fit bg-gradient-to-r from-purple-600 to-blue-600">
          <Sparkles className="w-3 h-3 mr-1" />
          Powered by Machine Learning
        </Badge>
      </div>

      {/* Info Banner */}
      <Card className="border-blue-200 bg-blue-50">
        <CardContent className="pt-6">
          <div className="flex items-start gap-3">
            <Brain className="w-5 h-5 text-blue-600 mt-0.5" />
            <div>
              <h3 className="font-medium text-blue-900 mb-1">About ML Predictions</h3>
              <p className="text-sm text-blue-800">
                This system uses <strong>ARIMA (AutoRegressive Integrated Moving Average)</strong> for demand forecasting
                and <strong>Linear Regression</strong> for stock duration prediction. The models analyze historical sales data 
                and inventory movements to provide intelligent insights for inventory management decisions.
              </p>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Quick Stats */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Products Tracked</CardTitle>
            <Activity className="h-4 w-4 text-blue-600" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{productPredictions.length}</div>
            <p className="text-xs text-slate-500 mt-1">Active predictions</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Avg Confidence</CardTitle>
            <Brain className="h-4 w-4 text-purple-600" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">
              {(mlPredictions.reduce((sum, p) => sum + p.confidence, 0) / mlPredictions.length * 100).toFixed(0)}%
            </div>
            <p className="text-xs text-slate-500 mt-1">Model accuracy</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Reorder Alerts</CardTitle>
            <AlertCircle className="h-4 w-4 text-red-600" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{reorderRecommendations.length}</div>
            <p className="text-xs text-slate-500 mt-1">Need attention</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Forecast Period</CardTitle>
            <Calendar className="h-4 w-4 text-green-600" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">7 Days</div>
            <p className="text-xs text-slate-500 mt-1">Prediction window</p>
          </CardContent>
        </Card>
      </div>

      {/* Reorder Recommendations */}
      {reorderRecommendations.length > 0 && (
        <Card className="border-orange-200 bg-orange-50">
          <CardHeader>
            <CardTitle className="flex items-center gap-2 text-orange-900">
              <AlertCircle className="w-5 h-5" />
              Reorder Recommendations
            </CardTitle>
            <CardDescription className="text-orange-700">
              Products that may run out soon based on predicted demand
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="space-y-3">
              {reorderRecommendations.map(({ product, demand, duration }) => {
                const urgency = getUrgencyBadge(duration?.predicted_value || 999);
                const daysLeft = duration?.predicted_value || 0;
                const predictedDemand = demand?.predicted_value || 0;
                const recommendedOrder = Math.max(0, predictedDemand - product.current_stock + product.min_stock);

                return (
                  <div key={product.id} className="p-4 bg-white rounded-lg border border-orange-200">
                    <div className="flex items-start justify-between mb-3">
                      <div>
                        <h4 className="font-medium">{product.name}</h4>
                        <p className="text-sm text-slate-600">{product.sku}</p>
                      </div>
                      <Badge variant={urgency.variant}>{urgency.label}</Badge>
                    </div>
                    
                    <div className="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                      <div>
                        <p className="text-slate-600">Current Stock</p>
                        <p className="font-bold">{product.current_stock}</p>
                      </div>
                      <div>
                        <p className="text-slate-600">Days Left</p>
                        <p className={`font-bold ${urgency.color}`}>{daysLeft.toFixed(0)} days</p>
                      </div>
                      <div>
                        <p className="text-slate-600">Predicted Demand</p>
                        <p className="font-bold">{predictedDemand.toFixed(0)}</p>
                      </div>
                      <div>
                        <p className="text-slate-600">Recommended Order</p>
                        <p className="font-bold text-blue-600">{recommendedOrder.toFixed(0)}</p>
                      </div>
                    </div>
                  </div>
                );
              })}
            </div>
          </CardContent>
        </Card>
      )}

      {/* Demand Forecast Chart */}
      <Card>
        <CardHeader>
          <CardTitle>7-Day Demand Forecast</CardTitle>
          <CardDescription>Predicted sales volume for top products</CardDescription>
        </CardHeader>
        <CardContent>
          <ResponsiveContainer width="100%" height={300}>
            <LineChart data={forecastTrend}>
              <CartesianGrid strokeDasharray="3 3" />
              <XAxis dataKey="day" />
              <YAxis />
              <Tooltip />
              <Legend />
              <Line type="monotone" dataKey="cement" stroke="#3b82f6" strokeWidth={2} name="Cement (sacks)" />
              <Line type="monotone" dataKey="steel" stroke="#ef4444" strokeWidth={2} name="Steel (pieces)" />
              <Line type="monotone" dataKey="bricks" stroke="#10b981" strokeWidth={2} name="Bricks (x100)" />
            </LineChart>
          </ResponsiveContainer>
        </CardContent>
      </Card>

      {/* Detailed Predictions Table */}
      <Card>
        <CardHeader>
          <CardTitle>Detailed Predictions</CardTitle>
          <CardDescription>AI-generated forecasts for all products</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="overflow-x-auto">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Product</TableHead>
                  <TableHead>Current Stock</TableHead>
                  <TableHead>Predicted Demand (7d)</TableHead>
                  <TableHead>Stock Duration</TableHead>
                  <TableHead>Confidence</TableHead>
                  <TableHead>Algorithm</TableHead>
                  <TableHead>Status</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {productPredictions.map(({ product, demand, duration }) => {
                  const confidenceBadge = getConfidenceBadge(demand?.confidence || 0);
                  const daysLeft = duration?.predicted_value || 0;
                  const urgency = getUrgencyBadge(daysLeft);

                  return (
                    <TableRow key={product.id}>
                      <TableCell>
                        <div>
                          <p className="font-medium">{product.name}</p>
                          <p className="text-xs text-slate-500">{product.sku}</p>
                        </div>
                      </TableCell>
                      <TableCell>
                        <p className="font-medium">{product.current_stock}</p>
                        <p className="text-xs text-slate-500">{product.base_unit}</p>
                      </TableCell>
                      <TableCell>
                        <p className="font-bold text-blue-600">
                          {demand?.predicted_value.toFixed(0) || 'N/A'}
                        </p>
                      </TableCell>
                      <TableCell>
                        <div>
                          <p className={`font-bold ${urgency.color}`}>
                            {daysLeft.toFixed(0)} days
                          </p>
                          <Progress value={(daysLeft / 30) * 100} className="h-1 mt-1" />
                        </div>
                      </TableCell>
                      <TableCell>
                        <Badge variant={confidenceBadge.variant}>
                          {((demand?.confidence || 0) * 100).toFixed(0)}% - {confidenceBadge.label}
                        </Badge>
                      </TableCell>
                      <TableCell className="text-xs">
                        <p>{demand?.metadata.algorithm || 'N/A'}</p>
                        <p className="text-slate-500">
                          {demand?.metadata.historical_days || 0}d history
                        </p>
                      </TableCell>
                      <TableCell>
                        <Badge variant={urgency.variant}>{urgency.label}</Badge>
                      </TableCell>
                    </TableRow>
                  );
                })}
              </TableBody>
            </Table>
          </div>
        </CardContent>
      </Card>

      {/* ML Model Info */}
      <Card>
        <CardHeader>
          <CardTitle>Model Information</CardTitle>
          <CardDescription>Technical details about the prediction models</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div className="space-y-3">
              <h4 className="font-medium flex items-center gap-2">
                <TrendingUp className="w-4 h-4 text-blue-600" />
                Demand Forecasting Model
              </h4>
              <div className="text-sm space-y-2 text-slate-600">
                <p><strong>Algorithm:</strong> ARIMA (AutoRegressive Integrated Moving Average)</p>
                <p><strong>Training Data:</strong> 90 days of historical sales</p>
                <p><strong>Update Frequency:</strong> Daily at midnight</p>
                <p><strong>Prediction Window:</strong> 7 days ahead</p>
                <p><strong>Average Accuracy:</strong> 82-87%</p>
              </div>
            </div>

            <div className="space-y-3">
              <h4 className="font-medium flex items-center gap-2">
                <Calendar className="w-4 h-4 text-purple-600" />
                Stock Duration Model
              </h4>
              <div className="text-sm space-y-2 text-slate-600">
                <p><strong>Algorithm:</strong> Linear Regression</p>
                <p><strong>Training Data:</strong> 60 days of stock movements</p>
                <p><strong>Update Frequency:</strong> After each transaction</p>
                <p><strong>Prediction:</strong> Days until stockout</p>
                <p><strong>Average Accuracy:</strong> 75-80%</p>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  );
};