import { useState } from 'react';
import { ScrollView, Alert } from 'react-native';
import { api } from '../../api/client';
import ScreenContainer from '../../components/ScreenContainer';
import TextField from '../../components/TextField';
import Button from '../../components/Button';
import { spacing } from '../../theme';

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
    <ScreenContainer>
      <ScrollView contentContainerStyle={{ padding: spacing.lg }}>
        <TextField
          label="О себе"
          value={bio}
          onChangeText={setBio}
          multiline
          numberOfLines={4}
          placeholder="Расскажите о себе"
          style={{ minHeight: 90, textAlignVertical: 'top' }}
        />

        <TextField
          label="ID города (city_id)"
          value={cityId}
          onChangeText={setCityId}
          keyboardType="numeric"
          placeholder="1 — Туркменабад, 2 — Ашхабад..."
        />

        <TextField
          label="ID категории (category_id)"
          value={categoryId}
          onChangeText={setCategoryId}
          keyboardType="numeric"
          placeholder="1 — Сантехник, 2 — Электрик..."
        />

        <Button title="Сохранить" onPress={handleSave} loading={loading} style={{ marginTop: spacing.lg }} />
      </ScrollView>
    </ScreenContainer>
  );
}
