import { View, TouchableOpacity, StyleSheet, Text } from 'react-native';
import { usePathname, router } from 'expo-router';
import { MaterialIcons } from '@expo/vector-icons';
import { useColorScheme } from '@/hooks/useSettings';
import Colors from '@/constants/Colors';

export default function TabBar() {
  const pathname = usePathname();
  const colorScheme = useColorScheme();
  
  const tabs = [
    { name: 'home', label: 'Home', icon: 'home' },
    { name: 'filter', label: 'Filter', icon: 'filter-list' },
    { name: 'space', label: 'Spaces', icon: 'description' },
    { name: 'settings', label: 'Settings', icon: 'settings' },
  ];
  
  return (
    <View style={[
      styles.container, 
      { backgroundColor: Colors[`${colorScheme}`].background,
        borderTopColor: Colors[`${colorScheme}`].border,
      }
    ]}>
      {tabs.map(tab => {
        const isActive = pathname === `/(app)/${tab.name}`;
        return (
          <TouchableOpacity
            key={tab.name}
            style={styles.tab}
            onPress={() => {
              const route = `/(app)/${tab.name}` as any;
              router.push(route);
            }}
          >
            <MaterialIcons
              name={tab.icon as any}
              size={22}
              color={isActive ? Colors[`${colorScheme}`].primary : Colors[`${colorScheme}`].gray400}
            />
            <Text
              style={[
                styles.label,
                { color: isActive ? Colors[`${colorScheme}`].primary : Colors[`${colorScheme}`].gray400 }
              ]}
            >
              {tab.label}
            </Text>
          </TouchableOpacity>
        );
      })}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flexDirection: 'row',
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    height: 50,
    borderTopWidth: 1
  },
  tab: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingTop: 5,
  },
  label: {
    fontSize: 11,
    marginTop: 2,
  },
});
