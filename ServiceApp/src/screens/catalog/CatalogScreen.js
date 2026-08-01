import { useState, useEffect } from 'react';
import { View, Text, FlatList, StyleSheet, ActivityIndicator } from 'react-native';
import { fetchDirectory, getCachedDirectory } from '../../api/directoryCache';
import ScreenContainer from '../../components/ScreenContainer';
import Card from '../../components/Card';
import Chip from '../../components/Chip';
import CategoryIcon from '../../components/CategoryIcon';
import { colors, spacing, typography } from '../../theme';

export default function CatalogScreen({ navigation }) {
  const [cities, setCities] = useState([]);
  const [categories, setCategories] = useState([]);
  const [selectedCity, setSelectedCity] = useState(null);
  const [loading, setLoading] = useState(true);

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
    if (citiesCache || categoriesCache) setLoading(false);
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
    } finally {
      setLoading(false);
    }
  }

  function navigateToMasters(category) {
    navigation.navigate('MastersList', {
      category,
      cityId: selectedCity,
    });
  }

  if (loading) {
    return (
      <ScreenContainer style={styles.center}>
        <ActivityIndicator size="large" color={colors.primary} />
      </ScreenContainer>
    );
  }

  return (
    <ScreenContainer>
      <View style={styles.cityRow}>
        {cities.map((city) => (
          <Chip
            key={city.id}
            label={city.name_ru}
            active={selectedCity === city.id}
            onPress={() => setSelectedCity(city.id === selectedCity ? null : city.id)}
          />
        ))}
      </View>

      <FlatList
        data={categories}
        numColumns={2}
        contentContainerStyle={styles.grid}
        columnWrapperStyle={styles.gridRow}
        renderItem={({ item }) => (
          <Card style={styles.categoryCard} onPress={() => navigateToMasters(item)}>
            <CategoryIcon category={item} size={26} />
            <Text style={[typography.heading, styles.categoryName]}>{item.name_ru}</Text>
          </Card>
        )}
        keyExtractor={(item) => String(item.id)}
      />
    </ScreenContainer>
  );
}

const styles = StyleSheet.create({
  center: { justifyContent: 'center', alignItems: 'center', backgroundColor: colors.bg },
  cityRow: { flexDirection: 'row', flexWrap: 'wrap', padding: spacing.lg, gap: spacing.sm },
  grid: { padding: spacing.lg },
  gridRow: { justifyContent: 'space-between', marginBottom: spacing.lg },
  categoryCard: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    marginHorizontal: spacing.sm,
    paddingVertical: spacing.xl,
    borderRadius: 24,
    ...colors.shadows.small,
  },
  categoryName: { marginTop: spacing.md, textAlign: 'center', fontFamily: 'serif' },
});
