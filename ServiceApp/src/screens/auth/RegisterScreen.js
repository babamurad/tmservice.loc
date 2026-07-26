import { useState } from 'react';
import { View, Text, TouchableOpacity, StyleSheet, Alert, KeyboardAvoidingView, Platform } from 'react-native';
import { useAuth } from '../../hooks/useAuth';
import ScreenContainer from '../../components/ScreenContainer';
import TextField from '../../components/TextField';
import Chip from '../../components/Chip';
import Button from '../../components/Button';
import { colors, spacing, typography } from '../../theme';

const ROLES = [
  { label: 'Клиент', value: 'client' },
  { label: 'Мастер', value: 'master' },
];

export default function RegisterScreen({ navigation }) {
  const { register } = useAuth();
  const [phone, setPhone] = useState('');
  const [password, setPassword] = useState('');
  const [role, setRole] = useState('client');
  const [loading, setLoading] = useState(false);

  async function handleRegister() {
    if (!phone || !password) {
      Alert.alert('Ошибка', 'Заполните все поля');
      return;
    }
    setLoading(true);
    try {
      await register(phone, password, role);
    } catch (err) {
      Alert.alert('Ошибка', err.message?.[0] || 'Ошибка регистрации');
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
          <Text style={[typography.display, styles.title]}>Регистрация</Text>

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

          <Text style={[typography.caption, styles.roleLabel]}>Роль</Text>
          <View style={styles.roleRow}>
            {ROLES.map((r) => (
              <Chip key={r.value} label={r.label} active={role === r.value} onPress={() => setRole(r.value)} />
            ))}
          </View>

          <Button title="Зарегистрироваться" onPress={handleRegister} loading={loading} style={styles.button} />

          <TouchableOpacity onPress={() => navigation.goBack()}>
            <Text style={styles.link}>Уже есть аккаунт? Войти</Text>
          </TouchableOpacity>
        </View>
      </KeyboardAvoidingView>
    </ScreenContainer>
  );
}

const styles = StyleSheet.create({
  flex: { flex: 1 },
  container: { flex: 1, justifyContent: 'center', padding: spacing.xl },
  title: { textAlign: 'center', marginBottom: spacing.xxl },
  roleLabel: { marginBottom: spacing.sm },
  roleRow: { flexDirection: 'row', gap: spacing.sm, marginBottom: spacing.xxl },
  button: { marginBottom: spacing.lg },
  link: { color: colors.primary, textAlign: 'center', fontSize: 14, fontWeight: '600' },
});
