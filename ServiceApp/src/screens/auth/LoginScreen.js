import { useState } from 'react';
import { View, Text, TouchableOpacity, StyleSheet, Alert, KeyboardAvoidingView, Platform } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useAuth } from '../../hooks/useAuth';
import ScreenContainer from '../../components/ScreenContainer';
import TextField from '../../components/TextField';
import Button from '../../components/Button';
import { colors, spacing, typography } from '../../theme';

export default function LoginScreen({ navigation }) {
  const { login } = useAuth();
  const [phone, setPhone] = useState('');
  const [password, setPassword] = useState('');
  const [loading, setLoading] = useState(false);

  async function handleLogin() {
    if (!phone || !password) {
      Alert.alert('Ошибка', 'Заполните все поля');
      return;
    }
    setLoading(true);
    try {
      await login(phone, password);
    } catch (err) {
      Alert.alert('Ошибка', err.message?.[0] || 'Неверный телефон или пароль');
    } finally {
      setLoading(false);
    }
  }

  return (
    <ScreenContainer>
      <KeyboardAvoidingView
        style={styles.flex}
        behavior={Platform.OS === 'ios' ? 'padding' : undefined}
      >
        <View style={styles.container}>
          <View style={styles.coverPattern} />
          <View style={styles.badge}>
            <Ionicons name="hammer" size={40} color={colors.primary} />
          </View>
          <Text style={[typography.display, styles.title]}>Найди мастера</Text>
          <Text style={[typography.bodyMuted, styles.subtitle]}>
            Сантехник, электрик и другие мастера рядом с вами
          </Text>

          <TextField
            placeholder="Телефон"
            value={phone}
            onChangeText={setPhone}
            keyboardType="phone-pad"
            autoCapitalize="none"
          />
          <TextField
            placeholder="Пароль"
            value={password}
            onChangeText={setPassword}
            secureTextEntry
          />

          <Button title="Войти" onPress={handleLogin} loading={loading} style={styles.button} />

          <TouchableOpacity onPress={() => navigation.navigate('Register')}>
            <Text style={styles.link}>Нет аккаунта? Зарегистрироваться</Text>
          </TouchableOpacity>
        </View>
      </KeyboardAvoidingView>
    </ScreenContainer>
  );
}

const styles = StyleSheet.create({
  flex: { flex: 1, backgroundColor: colors.bg },
  container: { flex: 1, justifyContent: 'center', padding: spacing.xxl },
  coverPattern: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    height: 250,
    backgroundColor: colors.primaryDark,
    borderBottomLeftRadius: 40,
    borderBottomRightRadius: 40,
    opacity: 0.1,
  },
  badge: {
    alignSelf: 'center',
    width: 80,
    height: 80,
    borderRadius: 40,
    backgroundColor: colors.surface,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: spacing.xl,
    ...colors.shadows.medium,
  },
  title: { textAlign: 'center', fontFamily: 'serif' },
  subtitle: { textAlign: 'center', marginTop: spacing.xs, marginBottom: spacing.xxl, lineHeight: 22 },
  button: { marginTop: spacing.md, marginBottom: spacing.xl },
  link: { color: colors.primary, textAlign: 'center', fontSize: 15, fontWeight: '700' },
});
