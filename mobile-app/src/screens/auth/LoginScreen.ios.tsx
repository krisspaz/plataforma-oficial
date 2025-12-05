import React, { useState } from 'react';
import { 
  View, 
  Text, 
  TextInput, 
  TouchableOpacity, 
  StyleSheet, 
  Platform,
  KeyboardAvoidingView,
  ScrollView,
  useColorScheme
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

/**
 * LoginScreen optimizado para iOS
 * 
 * Características iOS:
 * - Haptic feedback (requiere expo-haptics)
 * - Respeta modo oscuro del sistema
 * - KeyboardAvoidingView para iOS
 * - Fuentes del sistema iOS
 * - Sombras iOS-native
 */
export default function LoginScreen() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const colorScheme = useColorScheme();
  const isDark = colorScheme === 'dark';

  const handleLogin = async () => {
    console.log('Login attempt', email, password);
    
    // Haptic feedback (descomentar si instalas expo-haptics)
    // if (Platform.OS === 'ios') {
    //   await Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Medium);
    // }
    
    // TODO: Call API
  };

  // Colores dinámicos según tema
  const colors = {
    background: isDark ? '#000000' : '#f8fafc',
    cardBackground: isDark ? '#1c1c1e' : '#ffffff',
    text: isDark ? '#ffffff' : '#0f172a',
    textSecondary: isDark ? '#8e8e93' : '#64748b',
    inputBorder: isDark ? '#38383a' : '#e2e8f0',
    primary: '#2563eb',
  };

  return (
    <SafeAreaView 
      style={[styles.container, { backgroundColor: colors.background }]}
      edges={['top', 'bottom']}
    >
      <KeyboardAvoidingView
        behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
        style={styles.keyboardView}
      >
        <ScrollView 
          contentContainerStyle={styles.scrollContent}
          keyboardShouldPersistTaps="handled"
          showsVerticalScrollIndicator={false}
        >
          <View style={styles.content}>
            {/* Logo o título */}
            <View style={styles.header}>
              <Text style={[styles.title, { color: colors.text }]}>
                KPixelCraft
              </Text>
              <Text style={[styles.subtitle, { color: colors.textSecondary }]}>
                Plataforma Escolar
              </Text>
            </View>

            {/* Formulario */}
            <View style={[
              styles.formCard, 
              { 
                backgroundColor: colors.cardBackground,
                ...Platform.select({
                  ios: styles.shadowIOS,
                  android: styles.shadowAndroid,
                }),
              }
            ]}>
              {/* Email Input */}
              <View style={styles.inputGroup}>
                <Text style={[styles.label, { color: colors.textSecondary }]}>
                  Correo Electrónico
                </Text>
                <TextInput
                  style={[
                    styles.input,
                    {
                      backgroundColor: colors.cardBackground,
                      borderColor: colors.inputBorder,
                      color: colors.text,
                    },
                  ]}
                  placeholder="ejemplo@colegio.edu"
                  placeholderTextColor={colors.textSecondary}
                  value={email}
                  onChangeText={setEmail}
                  autoCapitalize="none"
                  keyboardType="email-address"
                  textContentType="emailAddress" // iOS autocomplete
                  autoComplete="email" // iOS 15+
                />
              </View>

              {/* Password Input */}
              <View style={styles.inputGroup}>
                <Text style={[styles.label, { color: colors.textSecondary }]}>
                  Contraseña
                </Text>
                <TextInput
                  style={[
                    styles.input,
                    {
                      backgroundColor: colors.cardBackground,
                      borderColor: colors.inputBorder,
                      color: colors.text,
                    },
                  ]}
                  placeholder="••••••••"
                  placeholderTextColor={colors.textSecondary}
                  value={password}
                  onChangeText={setPassword}
                  secureTextEntry
                  textContentType="password" // iOS autocomplete
                  autoComplete="password" // iOS 15+
                />
              </View>

              {/* Olvidé mi contraseña */}
              <TouchableOpacity 
                style={styles.forgotPassword}
                activeOpacity={0.7}
              >
                <Text style={[styles.forgotPasswordText, { color: colors.primary }]}>
                  ¿Olvidaste tu contraseña?
                </Text>
              </TouchableOpacity>

              {/* Botón Login */}
              <TouchableOpacity
                style={[styles.button, { backgroundColor: colors.primary }]}
                onPress={handleLogin}
                activeOpacity={0.8} // Efecto iOS
              >
                <Text style={styles.buttonText}>Iniciar Sesión</Text>
              </TouchableOpacity>
            </View>

            {/* Footer */}
            <View style={styles.footer}>
              <Text style={[styles.footerText, { color: colors.textSecondary }]}>
                ¿No tienes cuenta?{' '}
                <Text style={{ color: colors.primary, fontWeight: '600' }}>
                  Regístrate
                </Text>
              </Text>
            </View>
          </View>
        </ScrollView>
      </KeyboardAvoidingView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  keyboardView: {
    flex: 1,
  },
  scrollContent: {
    flexGrow: 1,
    justifyContent: 'center',
  },
  content: {
    padding: 24,
  },
  header: {
    alignItems: 'center',
    marginBottom: 40,
  },
  title: {
    fontSize: 34, // iOS Large Title
    fontWeight: Platform.select({
      ios: '700',
      android: 'bold',
    }),
    marginBottom: 8,
    letterSpacing: Platform.OS === 'ios' ? 0.4 : 0,
  },
  subtitle: {
    fontSize: 17, // iOS Body
    fontWeight: Platform.select({
      ios: '400',
      android: 'normal',
    }),
  },
  formCard: {
    borderRadius: 16,
    padding: 24,
    marginBottom: 24,
  },
  shadowIOS: {
    shadowColor: '#000',
    shadowOffset: {
      width: 0,
      height: 2,
    },
    shadowOpacity: 0.1,
    shadowRadius: 8,
  },
  shadowAndroid: {
    elevation: 4,
  },
  inputGroup: {
    marginBottom: 20,
  },
  label: {
    fontSize: 13, // iOS Footnote
    fontWeight: Platform.select({
      ios: '600',
      android: 'bold',
    }),
    marginBottom: 8,
    letterSpacing: Platform.OS === 'ios' ? -0.08 : 0,
  },
  input: {
    borderWidth: 1,
    borderRadius: 12,
    padding: Platform.select({
      ios: 16,
      android: 14,
    }),
    fontSize: 17, // iOS Body
    fontFamily: Platform.select({
      ios: 'System',
      android: 'Roboto',
    }),
  },
  forgotPassword: {
    alignSelf: 'flex-end',
    marginBottom: 24,
  },
  forgotPasswordText: {
    fontSize: 15,
    fontWeight: Platform.select({
      ios: '600',
      android: 'bold',
    }),
  },
  button: {
    padding: Platform.select({
      ios: 18,
      android: 16,
    }),
    borderRadius: 12,
    alignItems: 'center',
  },
  buttonText: {
    color: '#ffffff',
    fontSize: 17,
    fontWeight: Platform.select({
      ios: '600',
      android: 'bold',
    }),
    letterSpacing: Platform.OS === 'ios' ? -0.4 : 0,
  },
  footer: {
    alignItems: 'center',
  },
  footerText: {
    fontSize: 15,
  },
});
