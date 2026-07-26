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
          <View style={styles.badge}>
            <Ionicons name="hammer" size={32} color={colors.primary} />
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
  flex: { flex: 1 },
  container: { flex: 1, justifyContent: 'center', padding: spacing.xl },
  badge: {
    alignSelf: 'center',
    width: 64,
    height: 64,
    borderRadius: 32,
    backgroundColor: colors.primaryLight,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: spacing.lg,
  },
  title: { textAlign: 'center' },
  subtitle: { textAlign: 'center', marginTop: spacing.xs, marginBottom: spacing.xxl },
  button: { marginTop: spacing.sm, marginBottom: spacing.lg },
  link: { color: colors.primary, textAlign: 'center', fontSize: 14, fontWeight: '600' },
});
