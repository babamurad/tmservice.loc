import { useEffect, useState } from 'react';
import { ScrollView, Alert, View, Text, StyleSheet } from 'react-native';
import { api } from '../../api/client';
import { fetchDirectory, getCachedDirectory } from '../../api/directoryCache';
import ScreenContainer from '../../components/ScreenContainer';
import TextField from '../../components/TextField';
import Button from '../../components/Button';
import Chip from '../../components/Chip';
import { spacing, typography } from '../../theme';

export default function EditProfileScreen({ route, navigation }) {
  const profile = route.params?.profile || {};
  const [bio, setBio] = useState(profile.bio || '');
  const [cityId, setCityId] = useState(profile.city_id || null);
  const [categoryId, setCategoryId] = useState(profile.category_id || null);
  const [cities, setCities] = useState([]);
  const [categories, setCategories] = useState([]);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    loadFromCache();
    refreshFromServer();
  }, []);

  async function loadFromCache() {
    const [citiesCache, categoriesCache] = await Promise.all([
      getCachedDirectory('cities'),
      getCachedDirectory('categories'),
    ]);

    if (citiesCache) setCities(citiesCache);
    if (categoriesCache) setCategories(categoriesCache);
  }

  async function refreshFromServer() {
    try {
      const [citiesData, categoriesData] = await Promise.all([
        fetchDirectory('/cities', 'cities'),
        fetchDirectory('/categories', 'categories'),
      ]);
      setCities(citiesData);
      setCategories(categoriesData);
    } catch (err) {
      console.error(err);
    }
  }

  // Название посёлка само по себе может быть неоднозначным — подписываем
  // головным городом, как в админке (см. plan/README.md про посёлки-спутники).
  function cityLabel(city) {
    if (!city.parent_city_id) return city.name_ru;

    const parent = cities.find((c) => c.id === city.parent_city_id);

    return parent ? `${city.name_ru} (${parent.name_ru})` : city.name_ru;
  }

  async function handleSave() {
    setLoading(true);
    try {
      await api('/profile/update', {
        method: 'POST',
        body: JSON.stringify({ bio, city_id: cityId, category_id: categoryId }),
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

        <Text style={[typography.heading, styles.label]}>Город</Text>
        <View style={styles.row}>
          {cities.map((city) => (
            <Chip
              key={city.id}
              label={cityLabel(city)}
              active={cityId === city.id}
              onPress={() => setCityId(city.id === cityId ? null : city.id)}
            />
          ))}
        </View>

        <Text style={[typography.heading, styles.label]}>Категория</Text>
        <View style={styles.row}>
          {categories.map((category) => (
            <Chip
              key={category.id}
              label={category.name_ru}
              active={categoryId === category.id}
              onPress={() => setCategoryId(category.id === categoryId ? null : category.id)}
            />
          ))}
        </View>

        <Button title="Сохранить" onPress={handleSave} loading={loading} style={{ marginTop: spacing.lg }} />
      </ScrollView>
    </ScreenContainer>
  );
}

const styles = StyleSheet.create({
  label: { marginTop: spacing.lg, marginBottom: spacing.sm },
  row: { flexDirection: 'row', flexWrap: 'wrap', gap: spacing.sm },
});
