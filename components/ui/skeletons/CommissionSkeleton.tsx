import React from 'react';
import { View, StyleSheet } from 'react-native';
import Colors, { Color } from '@/constants/Colors';

export default function CommissionSkeleton() {
  return (
    <View style={styles.container}>
      <View style={styles.iconContainer} />
      <View style={styles.content}>
        <View style={styles.nameSkeleton} />
        <View style={styles.details}>
          <View style={styles.detailItemSkeleton} />
        </View>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: Color.white,
    borderRadius: 8,
    marginBottom: 10,
    padding: 12,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 1,
    elevation: 1,
  },
  iconContainer: {
    width: 50,
    height: 50,
    borderRadius: 25,
    backgroundColor: Color.gray[200],
    marginRight: 12,
  },
  content: {
    flex: 1,
  },
  nameSkeleton: {
    height: 18,
    width: '60%',
    backgroundColor: Color.gray[200],
    borderRadius: 4,
    marginBottom: 8,
  },
  details: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    marginTop: 4,
  },
  detailItemSkeleton: {
    height: 12,
    width: '80%',
    backgroundColor: Color.gray[200],
    borderRadius: 4,
    marginTop: 4,
  },
}); 