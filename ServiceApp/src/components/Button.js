import { TouchableOpacity, Text, ActivityIndicator, StyleSheet } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { colors, radius, spacing, typography } from '../theme';

const VARIANTS = {
  primary: { bg: colors.primary, border: colors.primary, text: colors.white },
  danger: { bg: colors.danger, border: colors.danger, text: colors.white },
  success: { bg: colors.success, border: colors.success, text: colors.white },
  dark: { bg: colors.ink, border: colors.ink, text: colors.white },
  outline: { bg: 'transparent', border: colors.border, text: colors.ink },
};

export default function Button({
  title,
  onPress,
  variant = 'primary',
  icon,
  loading = false,
  disabled = false,
  style,
}) {
  const v = VARIANTS[variant] || VARIANTS.primary;

  return (
    <TouchableOpacity
      style={[
        styles.base,
        { backgroundColor: v.bg, borderColor: v.border },
        (disabled || loading) && styles.disabled,
        style,
      ]}
      onPress={onPress}
      disabled={disabled || loading}
      activeOpacity={0.8}
    >
      {loading ? (
        <ActivityIndicator color={v.text} />
      ) : (
        <>
          {icon && <Ionicons name={icon} size={18} color={v.text} style={styles.icon} />}
          <Text style={[styles.text, typography.button, { color: v.text }]}>{title}</Text>
        </>
      )}
    </TouchableOpacity>
  );
}

const styles = StyleSheet.create({
  base: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    borderWidth: 1,
    borderRadius: radius.md,
    paddingVertical: 14,
    paddingHorizontal: spacing.lg,
  },
  disabled: { opacity: 0.55 },
  icon: { marginRight: spacing.sm },
  text: {},
});
