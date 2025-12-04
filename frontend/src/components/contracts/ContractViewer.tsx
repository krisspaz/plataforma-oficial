import React, { useState, useEffect } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardFooter } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { SignaturePad } from './SignaturePad';
import { contractService, Contract } from '@/services/contract.service';
import { toast } from 'sonner';
import { Loader2, Download, CheckCircle, AlertCircle, FileText } from 'lucide-react';

interface ContractViewerProps {
    contractId: number;
    onClose?: () => void;
}

export const ContractViewer: React.FC<ContractViewerProps> = ({ contractId, onClose }) => {
    const [contract, setContract] = useState<Contract | null>(null);
    const [loading, setLoading] = useState(true);
    const [signing, setSigning] = useState(false);
    const [signature, setSignature] = useState<string | null>(null);
    const [signerName, setSignerName] = useState('');
    const [signerEmail, setSignerEmail] = useState('');

    useEffect(() => {
        loadContract();
    }, [contractId]);

    const loadContract = async () => {
        try {
            const data = await contractService.getContract(contractId);
            setContract(data);
        } catch (error) {
            toast.error('Error al cargar el contrato');
        } finally {
            setLoading(false);
        }
    };

    const handleDownload = async () => {
        try {
            await contractService.downloadContract(contractId);
            toast.success('Descarga iniciada');
        } catch (error) {
            toast.error('Error al descargar el documento');
        }
    };

    const handleSign = async () => {
        if (!signature || !signerName || !signerEmail) {
            toast.error('Por favor completa todos los campos de firma');
            return;
        }

        setSigning(true);
        try {
            await contractService.signContract(contractId, {
                signer_name: signerName,
                signer_email: signerEmail,
                signature_image: signature
            });

            toast.success('Contrato firmado exitosamente');
            loadContract(); // Reload to show signed status
        } catch (error) {
            toast.error('Error al firmar el contrato');
        } finally {
            setSigning(false);
        }
    };

    if (loading) {
        return (
            <div className="flex justify-center items-center h-64">
                <Loader2 className="w-8 h-8 animate-spin text-blue-600" />
            </div>
        );
    }

    if (!contract) {
        return (
            <div className="text-center p-8 text-slate-500">
                <AlertCircle className="w-12 h-12 mx-auto mb-4" />
                <p>No se encontró el contrato</p>
            </div>
        );
    }

    const statusBadge = contractService.getStatusBadge(contract.status);

    return (
        <div className="max-w-4xl mx-auto space-y-6">
            {/* Header */}
            <div className="flex justify-between items-start">
                <div>
                    <h2 className="text-2xl font-bold flex items-center gap-2">
                        <FileText className="w-6 h-6" />
                        Contrato {contract.contract_number}
                    </h2>
                    <p className="text-slate-500">
                        {contract.student_name} - {contract.grade}
                    </p>
                </div>
                <div className="flex gap-2">
                    <Badge className={statusBadge.color}>{statusBadge.label}</Badge>
                    <Button variant="outline" size="sm" onClick={handleDownload}>
                        <Download className="w-4 h-4 mr-2" />
                        Descargar PDF
                    </Button>
                </div>
            </div>

            {/* Contract Details */}
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                <Card className="md:col-span-2">
                    <CardHeader>
                        <CardTitle className="text-lg">Vista Previa del Documento</CardTitle>
                    </CardHeader>
                    <CardContent className="bg-slate-100 min-h-[400px] flex items-center justify-center rounded-md mx-6 mb-6">
                        {/* In a real app, this would be a PDF viewer component */}
                        <div className="text-center text-slate-500">
                            <FileText className="w-16 h-16 mx-auto mb-4 opacity-50" />
                            <p>Vista previa del PDF</p>
                            <Button variant="link" onClick={handleDownload}>
                                Descargar para ver completo
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <div className="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-sm font-medium uppercase text-slate-500">
                                Detalles Financieros
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div>
                                <span className="text-sm text-slate-500">Monto Total</span>
                                <p className="text-xl font-bold">
                                    {contractService.formatCurrency(contract.total_amount)}
                                </p>
                            </div>
                            <div>
                                <span className="text-sm text-slate-500">Plan de Pagos</span>
                                <p className="font-medium">{contract.installments} cuotas</p>
                            </div>
                        </CardContent>
                    </Card>

                    {!contract.is_signed ? (
                        <Card className="border-blue-200 bg-blue-50">
                            <CardHeader>
                                <CardTitle className="text-lg text-blue-800">Firma Digital</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="space-y-2">
                                    <Label htmlFor="signerName">Nombre del Firmante</Label>
                                    <Input
                                        id="signerName"
                                        value={signerName}
                                        onChange={(e) => setSignerName(e.target.value)}
                                        placeholder="Nombre completo"
                                        className="bg-white"
                                    />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="signerEmail">Correo Electrónico</Label>
                                    <Input
                                        id="signerEmail"
                                        type="email"
                                        value={signerEmail}
                                        onChange={(e) => setSignerEmail(e.target.value)}
                                        placeholder="correo@ejemplo.com"
                                        className="bg-white"
                                    />
                                </div>

                                <div className="space-y-2">
                                    <Label>Firma</Label>
                                    <SignaturePad
                                        onSign={setSignature}
                                        className="bg-white border-slate-200"
                                    />
                                </div>
                            </CardContent>
                            <CardFooter>
                                <Button
                                    className="w-full"
                                    onClick={handleSign}
                                    disabled={signing || !signature}
                                >
                                    {signing && <Loader2 className="w-4 h-4 mr-2 animate-spin" />}
                                    Firmar Contrato
                                </Button>
                            </CardFooter>
                        </Card>
                    ) : (
                        <Card className="border-green-200 bg-green-50">
                            <CardContent className="pt-6 text-center space-y-4">
                                <div className="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto">
                                    <CheckCircle className="w-6 h-6 text-green-600" />
                                </div>
                                <div>
                                    <h3 className="font-bold text-green-800">Contrato Firmado</h3>
                                    <p className="text-sm text-green-600 mt-1">
                                        Este documento ha sido firmado digitalmente y es legalmente vinculante.
                                    </p>
                                </div>
                                {contract.signature_metadata && (
                                    <div className="text-xs text-slate-500 bg-white/50 p-2 rounded text-left">
                                        <p>Firmado por: {contract.signature_metadata.signer_name}</p>
                                        <p>Fecha: {new Date(contract.signature_metadata.signed_at).toLocaleString()}</p>
                                        <p>IP: {contract.signature_metadata.ip_address}</p>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    )}
                </div>
            </div>
        </div>
    );
};
