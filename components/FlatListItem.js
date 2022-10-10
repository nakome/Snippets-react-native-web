import React from "react";
import { Link } from "react-router-native";
import { Text, View, StyleSheet } from "react-native";
import { Ionicons } from '@expo/vector-icons';

// theme options
import { ThemeStyleColors } from "../config/Theme";

export default function FlatListItem(props) {
  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <View style={styles.left}>
          <Text style={styles.title}>{props.data.title}</Text>
        </View>
        <View style={styles.right}>
          <Link to={`/preview/${props.data.uid}`} style={styles.btn}>
            <Text>
              <Ionicons name="code" size={16} color={ThemeStyleColors.warning} />
            </Text>
          </Link>
        </View>
      </View>
      <View style={styles.row}>
        <Text style={styles.txt}>{props.data.description}</Text>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    display: "flex",
    flexDirection: "column",
    marginTop: 5,
    marginLeft: 10,
    marginRight: 10,
    marginBottom: 5,
    borderStyle: "solid",
    borderColor: ThemeStyleColors.primary,
    backgroundColor: ThemeStyleColors.secondary,
    borderWidth: 1,
  },
  header: {
    display: "flex",
    flexDirection: "row",
    backgroundColor: ThemeStyleColors.primary,
    borderBottomColor: ThemeStyleColors.info,
    borderBottomWidth: 3,
  },
  row: {
    display: "flex",
    flexDirection: "row",
  },
  left: {
    display: "flex",
    alignItems: "center",
    justifyContent: "flex-start",
    flexDirection: "row",
    flex: 1,
  },
  right: {
    display: "flex",
    alignItems: "center",
    justifyContent: "flex-end",
    flexDirection: "row",
    flex: 1,
  },
  title: {
    padding: 5,
    fontSize: 16,
    fontWeight: 'bold',
    textTransform: "capitalize",
    color: ThemeStyleColors.warning,
  },
  btn: {
    display: "flex",
    alignItems: "center",
    justifyContent: "center",
    width: 35,
    height: 30,
    padding: 8,
    margin: 5,
    borderRadius: 4,
    backgroundColor: ThemeStyleColors.secondary,
  },
  txt: {
    marginTop: 5,
    marginBottom: 10,
    padding: 5,
    fontSize: 16,
    color: ThemeStyleColors.warning,
  },
});
