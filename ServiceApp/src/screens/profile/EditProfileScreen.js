import { useState } from 'react';
import {
  View,
  Text,
  TextInput,
  TouchableOpacity,
  StyleSheet,
  Alert,
  ActivityIndicator,
  ScrollView,
} from 'react-native';
import { api } from '../../api/client';

export default function EditProfileScreen({ route, navigation }) {
  const { profile } = route.params;
  const [bio, setBio] = useState(profile.bio || '');
  const [cityId, setCityId] = useState(String(profile.city_id || ''));
  const [categoryId, setCategoryId] = useState(String(profile.category_id || ''));
  const [loading, setLoading] = useState(false);

  async function handleSave() {
    setLoading(true);
    try {
      await api('/profile/update', {
        method: 'POST',
        body: JSON.stringify({
          bio,
          city_id: cityId ? Number(cityId) : null,
          category_id: categoryId ? Number(categoryId) : null,
        }),
      });
      Alert.alert('Сохранено', 'Профиль обновлён');
      navigation.goBack();
    } catch (err) {
      Alert.alert('Ошибка', 'Не удалось сохранить');
    } finally {
      setLoading(false);
    }
  }

  return (
    <ScrollView contentContainerStyle={styles.container}>
      <Text style={styles.label}>О себе</Text>
      <TextInput
        style={styles.input}
        value={bio}
        onChangeText={setBio}
        multiline
        numberOfLines={4}
        placeholder="Расскажите о себе"
      />

      <Text style={styles.label}>ID города (city_id)</Text>
      <TextInput
        style={styles.input}
        value={cityId}
        onChangeText={setCityId}
        keyboardType="numeric"
        placeholder="1 — Туркменабад, 2 — Ашхабад..."
      />

      <Text style={styles.label}>ID категории (category_id)</Text>
      <TextInput
        style={styles.input}
        value={categoryId}
        onChangeText={setCategoryId}
        keyboardType="numeric"
        placeholder="1 — Сантехник, 2 — Электрик..."
      />

      <TouchableOpacity style={styles.button} onPress={handleSave} disabled={loading}>
        {loading ? (
          <ActivityIndicator color="#fff" />
        ) : (
          <Text style={styles.buttonText}>Сохранить</Text>
        )}
      </TouchableOpacity>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: { padding: 16 },
  label: { fontSize: 14, fontWeight: '600', marginBottom: 6, marginTop: 12 },
  input: {
    borderWidth: 1,
    borderColor: '#ccc',
    borderRadius: 8,
    padding: 12,
    fontSize: 16,
    marginBottom: 6,
  },
  button: {
    backgroundColor: '#007AFF',
    padding: 16,
    borderRadius: 10,
    alignItems: 'center',
    marginTop: 24,
  },
  buttonText: { color: '#fff', fontSize: 16, fontWeight: '600' },
});
