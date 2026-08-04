import { useState, useEffect } from 'react';
import { View, Text, FlatList, StyleSheet, ActivityIndicator } from 'react-native';
import { api } from '../../api/client';
import { getCachedDirectory } from '../../api/directoryCache';
import ScreenContainer from '../../components/ScreenContainer';
import Card from '../../components/Card';
import Chip from '../../components/Chip';
import TextField from '../../components/TextField';
import Button from '../../components/Button';
import CategoryIcon from '../../components/CategoryIcon';
import StatusBadge from '../../components/StatusBadge';
import RatingStars from '../../components/RatingStars';
import EmptyState from '../../components/EmptyState';
import { colors, spacing, typography } from '../../theme';

export default function SearchScreen({ navigation }) {
  const [query, setQuery] = useState('');
  const [cities, setCities] = useState([]);
  const [categories, setCategories] = useState([]);
  const [selectedCity, setSelectedCity] = useState(null);
  const [selectedCategory, setSelectedCategory] = useState(null);
  const [results, setResults] = useState([]);
  const [loading, setLoading] = useState(false);
  const [searched, setSearched] = useState(false);

  useEffect(() => {
    (async () => {
      const [citiesCache, categoriesCache] = await Promise.all([
        getCachedDirectory('cities'),
        getCachedDirectory('categories'),
      ]);
      if (citiesCache) setCities(citiesCache);
      if (categoriesCache) setCategories(categoriesCache);
    })();
  }, []);

  async function runSearch() {
    if (!query.trim() && !selectedCity && !selectedCategory) {
      setResults([]);
      setSearched(false);
      return;
    }

    setLoading(true);
    setSearched(true);

    try {
      const params = new URLSearchParams();
      if (query.trim()) params.append('q', query.trim());
      if (selectedCity) params.append('city_id', selectedCity);
      if (selectedCategory) params.append('category_id', selectedCategory);

      const data = await api(`/masters?${params}`);
      setResults(data?.data || (Array.isArray(data) ? data : []));
    } catch (err) {
      console.error(err);
      setResults([]);
    } finally {
      setLoading(false);
    }
  }

  function toggleCity(cityId) {
    setSelectedCity((prev) => (prev === cityId ? null : cityId));
  }

  function toggleCategory(categoryId) {
    setSelectedCategory((prev) => (prev === categoryId ? null : categoryId));
  }

  return (
    <ScreenContainer>
      <View style={styles.searchBar}>
        <TextField
          style={styles.searchInput}
          placeholder="Например: сантехник, ремонт крана"
          value={query}
          onChangeText={setQuery}
          onSubmitEditing={runSearch}
          returnKeyType="search"
        />
      </View>

      {cities.length > 0 && (
        <View style={styles.filterRow}>
          {/* Посёлки-спутники не показываем отдельными чипами — выбор
              головного города на бэкенде уже включает мастеров из них. */}
          {cities.filter((city) => !city.parent_city_id).map((city) => (
            <Chip key={city.id} label={city.name_ru} active={selectedCity === city.id} onPress={() => toggleCity(city.id)} />
          ))}
        </View>
      )}

      {categories.length > 0 && (
        <View style={styles.filterRow}>
          {categories.map((category) => (
            <Chip
              key={category.id}
              label={category.name_ru}
              active={selectedCategory === category.id}
              onPress={() => toggleCategory(category.id)}
            />
          ))}
        </View>
      )}

      <Button title="Найти" icon="search" onPress={runSearch} style={styles.searchButton} />

      {loading ? (
        <View style={styles.center}>
          <ActivityIndicator size="large" color={colors.primary} />
        </View>
      ) : (
        <FlatList
          data={results}
          contentContainerStyle={styles.list}
          renderItem={({ item }) => (
            <Card style={styles.card} onPress={() => navigation.navigate('MasterDetail', { master: item })}>
              <View style={styles.cardHeader}>
                <CategoryIcon category={item.category} size={20} />
                <View style={styles.cardHeaderInfo}>
                  <Text style={typography.heading}>{item.category?.name_ru || 'Мастер'}</Text>
                  {item.reviews_count > 0 && (
                    <RatingStars rating={item.avg_rating} reviewsCount={item.reviews_count} size={13} />
                  )}
                </View>
                <StatusBadge isFree={item.is_free} compact />
              </View>
              <Text style={[typography.bodyMuted, styles.bio]} numberOfLines={2}>
                {item.bio || 'Нет описания'}
              </Text>
              {item.city && <Text style={typography.caption}>📍 {item.city.name_ru}</Text>}
            </Card>
          )}
          keyExtractor={(item) => String(item.id)}
          ListEmptyComponent={
            searched ? (
              <EmptyState icon="search-outline" title="Ничего не найдено" subtitle="Попробуйте изменить запрос или фильтры" />
            ) : (
              <EmptyState icon="search" title="Найдите нужного мастера" subtitle="Введите запрос или выберите город и категорию" />
            )
          }
        />
      )}
    </ScreenContainer>
  );
}

const styles = StyleSheet.create({
  center: { flex: 1, justifyContent: 'center', alignItems: 'center', paddingTop: 40 },
  searchBar: { paddingHorizontal: spacing.lg, paddingTop: spacing.lg },
  searchInput: { marginBottom: 0 },
  filterRow: { flexDirection: 'row', flexWrap: 'wrap', paddingHorizontal: spacing.lg, gap: spacing.sm, marginBottom: spacing.sm },
  searchButton: { marginHorizontal: spacing.lg, marginVertical: spacing.md },
  list: { padding: spacing.lg, paddingTop: 0 },
  card: { marginBottom: spacing.md },
  cardHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.md,
    marginBottom: spacing.sm,
  },
  cardHeaderInfo: { flex: 1, gap: 2 },
  bio: { marginBottom: spacing.xs },
});
