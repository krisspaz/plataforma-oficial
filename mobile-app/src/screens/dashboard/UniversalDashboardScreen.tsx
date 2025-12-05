import React from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  useColorScheme,
} from 'react-native';
import ResponsiveContainer from '../../components/ResponsiveContainer';
import ResponsiveGrid from '../../components/ResponsiveGrid';
import SplitLayout from '../../components/SplitLayout';
import { useResponsiveDimensions } from '../../hooks/useResponsiveDimensions';
import { FontSize, ResponsiveSpacing } from '../../utils/deviceInfo';

/**
 * Ejemplo de Dashboard que se adapta perfectamente a todos los dispositivos
 * 
 * iPhone: Grid de 2 columnas
 * iPad portrait: Grid de 3 columnas
 * iPad landscape: Sidebar + Grid de 3-4 columnas
 */
export default function UniversalDashboardScreen() {
  const colorScheme = useColorScheme();
  const isDark = colorScheme === 'dark';
  const { isTablet, isLandscape } = useResponsiveDimensions();

  const colors = {
    background: isDark ? '#000000' : '#f8fafc',
    cardBackground: isDark ? '#1c1c1e' : '#ffffff',
    text: isDark ? '#ffffff' : '#0f172a',
    textSecondary: isDark ? '#8e8e93' : '#64748b',
    border: isDark ? '#38383a' : '#e2e8f0',
  };

  // Mock data
  const dashboardCards = [
    { id: 1, title: 'Notas', value: '8.5', color: '#2563eb' },
    { id: 2, title: 'Asistencia', value: '95%', color: '#10b981' },
    { id: 3, title: 'Tareas', value: '12', color: '#f59e0b' },
    { id: 4, title: 'Mensajes', value: '3', color: '#8b5cf6' },
    { id: 5, title: 'Próximos Eventos', value: '5', color: '#ef4444' },
    { id: 6, title: 'Horas de Estudio', value: '24h', color: '#06b6d4' },
  ];

  // Sidebar content
  const renderSidebar = () => (
    <View style={[styles.sidebar, { backgroundColor: colors.cardBackground }]}>
      <Text style={[styles.sidebarTitle, { color: colors.text }]}>
        Navegación
      </Text>
      {['Dashboard', 'Notas', 'Tareas', 'Calendario', 'Perfil'].map((item) => (
        <TouchableOpacity
          key={item}
          style={[styles.sidebarItem, { borderBottomColor: colors.border }]}
          activeOpacity={0.7}
        >
          <Text style={[styles.sidebarItemText, { color: colors.textSecondary }]}>
            {item}
          </Text>
        </TouchableOpacity>
      ))}
    </View>
  );

  // Main content
  const renderContent = () => (
    <ScrollView
      style={{ flex: 1 }}
      contentContainerStyle={{ padding: ResponsiveSpacing.md }}
      showsVerticalScrollIndicator={false}
    >
      {/* Header */}
      <View style={styles.header}>
        <Text
          style={[
            styles.headerTitle,
            {
              color: colors.text,
              fontSize: isTablet ? FontSize.huge : FontSize.xxxl,
            },
          ]}
        >
          Dashboard
        </Text>
        <Text
          style={[
            styles.headerSubtitle,
            {
              color: colors.textSecondary,
              fontSize: isTablet ? FontSize.lg : FontSize.md,
            },
          ]}
        >
          Bienvenido de vuelta, Estudiante
        </Text>
      </View>

      {/* Responsive Grid */}
      <ResponsiveGrid
        minColumnWidth={isTablet ? (isLandscape ? 180 : 200) : 150}
        spacing={ResponsiveSpacing.md}
      >
        {dashboardCards.map((card) => (
          <TouchableOpacity
            key={card.id}
            style={[
              styles.card,
              {
                backgroundColor: colors.cardBackground,
                borderColor: colors.border,
                padding: isTablet ? ResponsiveSpacing.lg : ResponsiveSpacing.md,
              },
            ]}
            activeOpacity={0.7}
          >
            <View
              style={[
                styles.cardIcon,
                {
                  backgroundColor: card.color + '20',
                  width: isTablet ? 60 : 48,
                  height: isTablet ? 60 : 48,
                },
              ]}
            >
              <Text style={{ fontSize: isTablet ? 28 : 24 }}>📊</Text>
            </View>
            <Text
              style={[
                styles.cardValue,
                {
                  color: colors.text,
                  fontSize: isTablet ? FontSize.xxxl : FontSize.xxl,
                },
              ]}
            >
              {card.value}
            </Text>
            <Text
              style={[
                styles.cardTitle,
                {
                  color: colors.textSecondary,
                  fontSize: isTablet ? FontSize.lg : FontSize.md,
                },
              ]}
            >
              {card.title}
            </Text>
          </TouchableOpacity>
        ))}
      </ResponsiveGrid>

      {/* Recent Activity Section */}
      <View style={[styles.section, { marginTop: ResponsiveSpacing.xl }]}>
        <Text
          style={[
            styles.sectionTitle,
            {
              color: colors.text,
              fontSize: isTablet ? FontSize.xxl : FontSize.xl,
            },
          ]}
        >
          Actividad Reciente
        </Text>
        {[1, 2, 3, 4].map((item) => (
          <TouchableOpacity
            key={item}
            style={[
              styles.activityItem,
              {
                backgroundColor: colors.cardBackground,
                borderColor: colors.border,
                padding: isTablet ? ResponsiveSpacing.lg : ResponsiveSpacing.md,
              },
            ]}
            activeOpacity={0.7}
          >
            <View style={styles.activityContent}>
              <Text
                style={[
                  styles.activityTitle,
                  {
                    color: colors.text,
                    fontSize: isTablet ? FontSize.lg : FontSize.md,
                  },
                ]}
              >
                Actividad #{item}
              </Text>
              <Text
                style={[
                  styles.activityDescription,
                  {
                    color: colors.textSecondary,
                    fontSize: isTablet ? FontSize.md : FontSize.sm,
                  },
                ]}
              >
                Descripción de la actividad reciente
              </Text>
            </View>
            <Text
              style={[
                styles.activityTime,
                {
                  color: colors.textSecondary,
                  fontSize: isTablet ? FontSize.md : FontSize.sm,
                },
              ]}
            >
              Hace 2h
            </Text>
          </TouchableOpacity>
        ))}
      </View>
    </ScrollView>
  );

  // Use SplitLayout for iPad landscape
  if (isTablet && isLandscape) {
    return (
      <View style={[styles.container, { backgroundColor: colors.background }]}>
        <SplitLayout sidebar={renderSidebar()} content={renderContent()} />
      </View>
    );
  }

  // Regular layout for iPhone and iPad portrait
  return (
    <ResponsiveContainer style={{ backgroundColor: colors.background }}>
      {renderContent()}
    </ResponsiveContainer>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  sidebar: {
    padding: ResponsiveSpacing.lg,
  },
  sidebarTitle: {
    fontSize: FontSize.xl,
    fontWeight: '700',
    marginBottom: ResponsiveSpacing.lg,
  },
  sidebarItem: {
    paddingVertical: ResponsiveSpacing.md,
    borderBottomWidth: StyleSheet.hairlineWidth,
  },
  sidebarItemText: {
    fontSize: FontSize.lg,
    fontWeight: '500',
  },
  header: {
    marginBottom: ResponsiveSpacing.xl,
  },
  headerTitle: {
    fontWeight: '700',
    marginBottom: 8,
  },
  headerSubtitle: {
    fontWeight: '400',
  },
  card: {
    borderRadius: 16,
    borderWidth: 1,
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: {
      width: 0,
      height: 2,
    },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  cardIcon: {
    borderRadius: 12,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: ResponsiveSpacing.md,
  },
  cardValue: {
    fontWeight: '700',
    marginBottom: 4,
  },
  cardTitle: {
    fontWeight: '500',
    textAlign: 'center',
  },
  section: {
    marginBottom: ResponsiveSpacing.xl,
  },
  sectionTitle: {
    fontWeight: '700',
    marginBottom: ResponsiveSpacing.md,
  },
  activityItem: {
    borderRadius: 12,
    borderWidth: 1,
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: ResponsiveSpacing.sm,
  },
  activityContent: {
    flex: 1,
  },
  activityTitle: {
    fontWeight: '600',
    marginBottom: 4,
  },
  activityDescription: {
    fontWeight: '400',
  },
  activityTime: {
    fontWeight: '400',
    marginLeft: ResponsiveSpacing.sm,
  },
});
