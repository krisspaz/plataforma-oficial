import React, { Component, ErrorInfo, ReactNode } from 'react';
import { Button } from './ui/button';
import { Card } from './ui/card';
import { AlertCircle } from 'lucide-react';

interface Props {
    children: ReactNode;
    fallback?: ReactNode;
}

interface State {
    hasError: boolean;
    error: Error | null;
}

export class ErrorBoundary extends Component<Props, State> {
    public state: State = {
        hasError: false,
        error: null,
    };

    public static getDerivedStateFromError(error: Error): State {
        return { hasError: true, error };
    }

    public componentDidCatch(error: Error, errorInfo: ErrorInfo) {
        // Log error to error reporting service
        console.error('Uncaught error:', error, errorInfo);
    }

    private handleReset = () => {
        this.setState({ hasError: false, error: null });
    };

    public render() {
        if (this.state.hasError) {
            if (this.props.fallback) {
                return this.props.fallback;
            }

            return (
                <div className="min-h-screen flex items-center justify-center bg-background p-4">
                    <Card className="max-w-md w-full p-6 text-center">
                        <div className="flex justify-center mb-4">
                            <div className="w-16 h-16 rounded-full bg-destructive/10 flex items-center justify-center">
                                <AlertCircle className="w-8 h-8 text-destructive" />
                            </div>
                        </div>
                        <h2 className="text-2xl font-bold mb-2">Algo salió mal</h2>
                        <p className="text-muted-foreground mb-4">
                            Lo sentimos, ha ocurrido un error inesperado. Por favor, intenta recargar la página.
                        </p>
                        {this.state.error && (
                            <details className="mb-4 text-left">
                                <summary className="cursor-pointer text-sm text-muted-foreground hover:text-foreground">
                                    Detalles del error
                                </summary>
                                <pre className="mt-2 p-2 bg-muted rounded text-xs overflow-auto">
                                    {this.state.error.toString()}
                                </pre>
                            </details>
                        )}
                        <div className="flex gap-2 justify-center">
                            <Button onClick={this.handleReset} variant="outline">
                                Intentar de nuevo
                            </Button>
                            <Button onClick={() => window.location.href = '/'}>
                                Ir al inicio
                            </Button>
                        </div>
                    </Card>
                </div>
            );
        }

        return this.props.children;
    }
}
