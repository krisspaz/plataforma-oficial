import React, { useState } from 'react';
import {
  View,
  Text,
  TextInput,
  TouchableOpacity,
  StyleSheet,
  KeyboardAvoidingView,
  ScrollView,
  Platform,
  useColorScheme,
} from 'react-native';
import ResponsiveContainer from '../../components/ResponsiveContainer';
import { useResponsiveDimensions } from '../../hooks/useResponsiveDimensions';
import { useOrientation } from '../../hooks/useOrientation';
import { FontSize, ResponsiveSpacing } from '../../utils/deviceInfo';

/**
 * LoginScreen completamente responsivo para todos los dispositivos Apple
 * 
 * ✅ iPhone SE a iPhone 15 Pro Max
 * ✅ iPad mini a iPad Pro 12.9"
 * ✅ Portrait y Landscape
 * ✅ Dark Mode
 * ✅ SafeArea optimizado
 */
export default function UniversalLoginScreen() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  
  const colorScheme = useColorScheme();
  const isDark = colorScheme === 'dark';
  const { isTablet, isSmall, hasNotch } = useResponsiveDimensions();
  const orientation = useOrientation();

  const handleLogin = async () => {
    console.log('Login attempt', email, password);
    // TODO: Implement login logic
  };

  // Colores adaptativos
  const colors = {
    background: isDark ? '#000000' : '#f8fafc',
    cardBackground: isDark ? '#1c1c1e' : '#ffffff',
    text: isDark ? '#ffffff' : '#0f172a',
    textSecondary: isDark ? '#8e8e93' : '#64748b',
    inputBorder: isDark ? '#38383a' : '#e2e8f0',
    primary: '#2563eb',
  };

  // Espaciado adaptativo
  const spacing = isTablet ? ResponsiveSpacing.xl : ResponsiveSpacing.lg;
  const contentMaxWidth = isTablet ? 600 : undefined;

  return (
    <ResponsiveContainer
      style={{ backgroundColor: colors.background }}
      maxWidth={contentMaxWidth}
    >
      <KeyboardAvoidingView
        behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
        style={styles.keyboardView}
      >
        <ScrollView
          contentContainerStyle={[
            styles.scrollContent,
            {
              paddingHorizontal: spacing,
              paddingTop: hasNotch ? 60 : 40,
            },
          ]}
          keyboardShouldPersistTaps="handled"
          showsVerticalScrollIndicator={false}
        >
          {/* Header */}
          <View style={styles.header}>
            <Text
              style={[
                styles.title,
                {
                  color: colors.text,
                  fontSize: isTablet ? FontSize.huge : FontSize.xxxl,
                },
              ]}
            >
              KPixelCraft
            </Text>
            <Text
              style={[
                styles.subtitle,
                {
                  color: colors.textSecondary,
                  fontSize: isTablet ? FontSize.xl : FontSize.lg,
                },
              ]}
            >
              Plataforma Escolar
            </Text>
          </View>

          {/* Form Card */}
          <View
            style={[
              styles.formCard,
              {
                backgroundColor: colors.cardBackground,
                padding: isTablet ? ResponsiveSpacing.xl : ResponsiveSpacing.lg,
                marginTop: isTablet ? 60 : 40,
                ...Platform.select({
                  ios: styles.shadowIOS,
                  android: styles.shadowAndroid,
                }),
              },
            ]}
          >
            {/* Email Input */}
            <View style={[styles.inputGroup, { marginBottom: ResponsiveSpacing.md }]}>
              <Text
                style={[
                  styles.label,
                  {
                    color: colors.textSecondary,
                    fontSize: isSmall ? FontSize.sm : FontSize.md,
                  },
                ]}
              >
                Correo Electrónico
              </Text>
              <TextInput
                style={[
                  styles.input,
                  {
                    backgroundColor: colors.cardBackground,
                    borderColor: colors.inputBorder,
                    color: colors.text,
                    fontSize: isTablet ? FontSize.xl : FontSize.lg,
                    padding: isTablet ? 20 : 16,
                  },
                ]}
                placeholder="ejemplo@colegio.edu"
                placeholderTextColor={colors.textSecondary}
                value={email}
                onChangeText={setEmail}
                autoCapitalize="none"
                keyboardType="email-address"
                textContentType="emailAddress"
                autoComplete="email"
              />
            </View>

            {/* Password Input */}
            <View style={[styles.inputGroup, { marginBottom: ResponsiveSpacing.md }]}>
              <Text
                style={[
                  styles.label,
                  {
                    color: colors.textSecondary,
                    fontSize: isSmall ? FontSize.sm : FontSize.md,
                  },
                ]}
              >
                Contraseña
              </Text>
              <TextInput
                style={[
                  styles.input,
                  {
                    backgroundColor: colors.cardBackground,
                    borderColor: colors.inputBorder,
                    color: colors.text,
                    fontSize: isTablet ? FontSize.xl : FontSize.lg,
                    padding: isTablet ? 20 : 16,
                  },
                ]}
                placeholder="••••••••"
                placeholderTextColor={colors.textSecondary}
                value={password}
                onChangeText={setPassword}
                secureTextEntry
                textContentType="password"
                autoComplete="password"
              />
            </View>

            {/* Forgot Password */}
            <TouchableOpacity
              style={[styles.forgotPassword, { marginBottom: ResponsiveSpacing.lg }]}
              activeOpacity={0.7}
            >
              <Text
                style={[
                  styles.forgotPasswordText,
                  {
                    color: colors.primary,
                    fontSize: isSmall ? FontSize.sm : FontSize.md,
                  },
                ]}
              >
                ¿Olvidaste tu contraseña?
              </Text>
            </TouchableOpacity>

            {/* Login Button */}
            <TouchableOpacity
              style={[
                styles.button,
                {
                  backgroundColor: colors.primary,
                  padding: isTablet ? 22 : 18,
                },
              ]}
              onPress={handleLogin}
              activeOpacity={0.8}
            >
              <Text
                style={[
                  styles.buttonText,
                  {
                    fontSize: isTablet ? FontSize.xl : FontSize.lg,
                  },
                ]}
              >
                Iniciar Sesión
              </Text>
            </TouchableOpacity>
          </View>

          {/* Footer */}
          <View style={[styles.footer, { marginTop: ResponsiveSpacing.xl }]}>
            <Text
              style={[
                styles.footerText,
                {
                  color: colors.textSecondary,
                  fontSize: isSmall ? FontSize.sm : FontSize.md,
                },
              ]}
            >
              ¿No tienes cuenta?{' '}
              <Text style={{ color: colors.primary, fontWeight: '600' }}>
                Regístrate
              </Text>
            </Text>
          </View>

          {/* Extra padding for small devices */}
          <View style={{ height: isSmall ? 40 : 20 }} />
        </ScrollView>
      </KeyboardAvoidingView>
    </ResponsiveContainer>
  );
}

const styles = StyleSheet.create({
  keyboardView: {
    flex: 1,
  },
  scrollContent: {
    flexGrow: 1,
    justifyContent: 'center',
  },
  header: {
    alignItems: 'center',
  },
  title: {
    fontWeight: Platform.select({
      ios: '700',
      android: 'bold',
    }) as any,
    marginBottom: 8,
    letterSpacing: Platform.OS === 'ios' ? 0.4 : 0,
  },
  subtitle: {
    fontWeight: Platform.select({
      ios: '400',
      android: 'normal',
    }) as any,
  },
  formCard: {
    borderRadius: 20,
  },
  shadowIOS: {
    shadowColor: '#000',
    shadowOffset: {
      width: 0,
      height: 4,
    },
    shadowOpacity: 0.15,
    shadowRadius: 12,
  },
  shadowAndroid: {
    elevation: 8,
  },
  inputGroup: {
    width: '100%',
  },
  label: {
    fontWeight: Platform.select({
      ios: '600',
      android: 'bold',
    }) as any,
    marginBottom: 8,
    letterSpacing: Platform.OS === 'ios' ? -0.08 : 0,
  },
  input: {
    borderWidth: 1,
    borderRadius: 14,
    fontFamily: Platform.select({
      ios: 'System',
      android: 'Roboto',
    }),
  },
  forgotPassword: {
    alignSelf: 'flex-end',
  },
  forgotPasswordText: {
    fontWeight: Platform.select({
      ios: '600',
      android: 'bold',
    }) as any,
  },
  button: {
    borderRadius: 14,
    alignItems: 'center',
    justifyContent: 'center',
  },
  buttonText: {
    color: '#ffffff',
    fontWeight: Platform.select({
      ios: '600',
      android: 'bold',
    }) as any,
    letterSpacing: Platform.OS === 'ios' ? -0.4 : 0,
  },
  footer: {
    alignItems: 'center',
  },
  footerText: {
    textAlign: 'center',
  },
});
